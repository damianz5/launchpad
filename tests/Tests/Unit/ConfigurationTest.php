<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license   For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace eZ\Launchpad\Tests\Unit;

use eZ\Launchpad\Configuration\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Configuration
 */
class ConfigurationTest extends TestCase
{

    protected static $defaultConfiguration = [
        'docker'            => [
            "compose_filename"        => "docker-compose.yml",
            "network_prefix_port"     => "42",
            "host_machine_mapping"    => null,
            "network_name"            => "default-ezlaunchpad",
            "host_composer_cache_dir" => null
        ],
        "provisioning"      => [
            "folder_name" => "provisioning"
        ],
        "last_update_check" => null
    ];

    protected function process($configs)
    {
        $processor     = new Processor();
        $configuration = new Configuration();

        return $processor->processConfiguration(
            $configuration,
            $configs
        );
    }

    public function testDefaultLoad()
    {
        $config = $this->process([]);
        $this->assertEquals(static::$defaultConfiguration, $config);
    }

    public function getYamlExamples()
    {
        return [
            ['', 'ok'],
            [
                '
last_update_check: 1491955697
provisioning:
    folder_name: provisio
docker:
    compose_filename: compose.yml
    network_name: something
    network_prefix_port: 43
            ', 'ok',
            ],
            [
                '
provisioning:
docker:
            ', 'default',
            ],
            [
                '
docker:
            ', 'default',
            ],
            [
                '
last_update_check: ~
            ', 'default',
            ],
            [
                '
provisioni2ng:
            ', 'exception',
            ],
        ];
    }

    /**
     * @dataProvider getYamlExamples
     *
     * @param $yaml
     * @param $expected
     */
    public function testYamlLoading($yaml, $expected)
    {
        if ($expected == 'exception') {
            return;
        }

        $configuration = Yaml::parse($yaml);
        $config        = $this->process([$configuration]);

        if ($expected == 'ok') {
            $this->assertInternalType('array', $config);
        }
        if ($expected == 'default') {
            $this->assertInternalType('array', $config);
            $this->assertEquals(static::$defaultConfiguration, $config);
        }
    }

    /**
     * @dataProvider getYamlExamples
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     *
     * @param $yaml
     * @param $expected
     */
    public function testYamlException($yaml, $expected)
    {
        $configuration = Yaml::parse($yaml);
        $this->process([$configuration]);
        if ($expected != 'exception') {
            throw new InvalidConfigurationException('mock');
        }
    }
}
