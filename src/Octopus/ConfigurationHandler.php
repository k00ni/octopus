<?php

namespace Octopus;

use Curl\Curl;
use Naucon\File\File;
use Saft\Rdf\NodeUtils;

class ConfigurationHandler
{
    /**
     * @var string
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $defaultRootFolder;

    /**
     * @var string
     */
    protected $configurationFilepath;

    /**
     * @var array
     */
    protected $repository = array();

    /**
     * @param string $defaultRootFolder
     */
    public function __construct($defaultRootFolder)
    {
        $this->defaultRootFolder = $defaultRootFolder;
    }

    /**
     * Extracts and collects information about all requirements, but does not download them.
     *
     * @param array $requirements
     */
    public function getRequirements(array $requirements, array $knownRequirements = array())
    {
        /*
         * requirements given by users octopus.json
         *
         *       |
         *       v
         *
         * e.g. w3c/rdf ===> "dublincore/elements": "*" ===> ...
         *              ===> "w3c/owl": "*"             ===> ...
         *              ===> "w3c/rdfs": "*"            ===> ...
         */
        foreach ($requirements as $requirement => $version) {
            // check, if we have information about all his used references in require-section
            // if we already know this requirement and all its required requirements
            // go to the next one.
            if (isset($this->repository[$requirement])
                && false == isset($knownRequirements[$requirement])) {

                $versions = $this->repository[$requirement]['version'];

                // the user does not care about a certain version (so we give him the latest)
                if ('*' == $version) {
                    // we found a * version
                    if (isset($versions['*'])) {
                        $versionEntry = $versions[$version];
                    // certain versions set, use the latest
                    } elseif (0 < count($versions)) {
                        // todo: use latest version
                        $versionEntry = array_shift($versions);
                    }
                }

                $knownRequirements[$requirement] = array(
                    'file' => $versionEntry['file'],
                    'prefix-uri' => $versionEntry['prefix-uri']
                );

                /*
                 * get requirements of the current reference
                 *
                 *                         |
                 *                         v
                 *
                 * e.g. w3c/rdf ===> "dublincore/elements": "*" ===> ...
                 *              ===> "w3c/owl": "*"             ===> ...
                 *              ===> "w3c/rdfs": "*"            ===> ...
                 */
                $knownRequirements = array_merge(
                    $knownRequirements,
                    $this->getRequirements(
                        $versionEntry['require'],
                        $knownRequirements
                    )
                );

            } elseif (false === isset($this->repository[$requirement])) {
                throw new \Exception('Unknown reference in use: '. $requirement);
            }
        }

        return $knownRequirements;
    }

    /**
     *
     */
    public function install()
    {
        // read configuration file
        $this->configuration = json_decode(file_get_contents($this->configurationFilepath), true);

        if (null == $this->configuration) {
            throw new \Exception('Configuration file contains invalid JSON.');
        }

        if (0 < count($this->configuration['version'])) {
            $requirements = array();
            foreach ($this->configuration['version'] as $version => $versionEntry) {
                $requirements = array_merge($requirements, $this->getRequirements($versionEntry['require']));
            }

            // set folder to install requirements
            if (isset($this->configuration['knowledge-directory'])) {
                $folderForRequirements = $this->defaultRootFolder
                    . $this->configuration['knowledge-directory']
                    . '/';
            } else {
                $folderForRequirements = $this->defaultRootFolder . '/knowledge/';
            }

            // if there are requirements to install, create knowledge directory first
            if (0 < count($requirements) && false == file_exists($folderForRequirements)) {
                $fileObject = new File($folderForRequirements);
                $fileObject->mkdirs();
            }

            $nodeUtils = new NodeUtils();
            foreach ($requirements as $name => $requirement) {
                // if a valid local file or URL was given
                if (file_exists($requirement['file'])) {
                    $fileObject = new File($requirement['file']);
                    $fileObject->copy($targetPath);

                // if a valid URL was given
                } elseif ($nodeUtils->simpleCheckURI($requirement['file'])) {
                    // remove all maybe existing files
                    if (file_exists($folderForRequirements . $name . '.ttl')) {
                        $fileObject = new File($folderForRequirements . $vendor . '/' . $project . '.ttl');
                        $fileObject->delete();
                    }

                    $curl = new Curl();
                    $curl->setOpt(CURLOPT_ENCODING , 'gzip');

                    // split name to be able to create all folders
                    $name = explode('/', $name);
                    $vendor = $name[0];
                    $project = $name[1];

                    if (false == file_exists($folderForRequirements . $vendor)) {
                        $fileObject = new File($folderForRequirements . $vendor);
                        $fileObject->mkdirs();
                    }

                    $curl->download(
                        $requirement['file'],
                        $folderForRequirements . $vendor . '/' . $project . '.ttl'
                    );
                }
            }

        } else {
            return 'No version information found. Did you added elements to version array?';
        }
    }

    /**
     * @param string $configurationFilepath
     * @param File $repositoryFileObject
     * @throws \Exception if configuration file does not exists
     * @throws \Exception if configuration file is not readable
     */
    public function setup($configurationFilepath, $repositoryFileObject)
    {
        if (false === file_exists($configurationFilepath)) {
            throw new \Exception('Given configuration file does not exists: ' . $configurationFilepath);
        }
        if (false === is_readable($configurationFilepath)) {
            throw new \Exception('Given configuration file is not readable: ' . $configurationFilepath);
        }

        $this->configurationFilepath = $configurationFilepath;

        // include known vocabulary and ontology meta data
        $iteratorObject = $repositoryFileObject->listAll();

        foreach ($iteratorObject as $vendorFolder) {
            $vendorFolder->getBasename() . '<br/>';
            if ($vendorFolder->isDir()) {
                foreach ($vendorFolder->listFiles() as $jsonFile) {
                    $infoArray = json_decode(file_get_contents($jsonFile->getPathname()), true);
                    $this->repository[$infoArray['name']] = $infoArray;
                }
            }
        }
    }
}
