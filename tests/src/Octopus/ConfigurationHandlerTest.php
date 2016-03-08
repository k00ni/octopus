<?php

namespace Octopus\Test;

use Naucon\File\File;
use Octopus\ConfigurationHandler;

class ConfigurationHandlerTests extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->fixture = new ConfigurationHandler();
        $this->testConfigurationFilepath = sys_get_temp_dir() . '/testconfigurationfile.json';
    }

    protected function createTestConfigurationFile()
    {
        file_put_contents(
            $this->testConfigurationFilepath,
            json_encode(
                array(
                    'name' => 'myvendor/myproject',
                    'version' => array(
                        '*' => array(
                            'require' => array(
                                'foo/bar1' => '*'
                            ),
                            'file' => '',
                            'prefix-uri' => ''
                        )
                    )
                )
            )
        );
    }

    protected function createTestRepository()
    {
        if (false == file_exists(sys_get_temp_dir() . '/octopus-repo/foo')) {
            $fileObject = new File(sys_get_temp_dir() . '/octopus-repo/foo');
            $fileObject->mkdirs();
        }

        // foo/bar1
        file_put_contents(
            sys_get_temp_dir() . '/octopus-repo/foo/bar1.json',
            json_encode(
                array(
                    'name' => 'foo/bar1',
                    'version' => array(
                        '*' => array(
                            'require' => array('foo/bar2' => '*'),
                            "file" => "https://foo/bar1.ttl",
                            "prefix-uri" => "http://foo/bar1/"
                        ),
                    )
                )
            )
        );

        // foo/bar2
        file_put_contents(
            sys_get_temp_dir() . '/octopus-repo/foo/bar2.json',
            json_encode(
                array(
                    'name' => 'foo/bar2',
                    'version' => array(
                        '*' => array(
                            "require" => array(
                                'foo/bar1' => '*',
                                'foo/bar3' => '*'
                            ),
                            "file" => "https://foo/bar2.ttl",
                            "prefix-uri" => "http://foo/bar2/"
                        ),
                    )
                )
            )
        );

        // foo/bar2
        file_put_contents(
            sys_get_temp_dir() . '/octopus-repo/foo/bar3.json',
            json_encode(
                array(
                    'name' => 'foo/bar3',
                    'version' => array(
                        '*' => array(
                            "require" => array(
                                'foo/bar2' => '*'
                            ),
                            "file" => "https://foo/bar3.ttl",
                            "prefix-uri" => "http://foo/bar3/"
                        ),
                    )
                )
            )
        );
    }

    /*
     * Tests for getRequirements
     */

    public function testGetRequirements()
    {
        $this->createTestConfigurationFile();
        $this->createTestRepository();

        $this->fixture->setup(
            $this->testConfigurationFilepath,
            new File(sys_get_temp_dir() . '/octopus-repo/')
        );

        $this->assertEquals(
            array(
                'foo/bar1' => array(
                    'file' => 'https://foo/bar1.ttl',
                    'prefix-uri' => 'http://foo/bar1/'
                ),
                'foo/bar2' => array(
                    'file' => 'https://foo/bar2.ttl',
                    'prefix-uri' => 'http://foo/bar2/'
                ),
                'foo/bar3' => array(
                    'file' => 'https://foo/bar3.ttl',
                    'prefix-uri' => 'http://foo/bar3/'
                ),
            ),
            $this->fixture->getRequirements(
                array('foo/bar1' => '*')
            )
        );
    }
}
