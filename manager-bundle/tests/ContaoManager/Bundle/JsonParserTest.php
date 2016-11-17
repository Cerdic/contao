<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\ManagerBundle\Test\Autoload;

use Contao\ManagerBundle\ContaoManager\Bundle\ConfigInterface;
use Contao\ManagerBundle\ContaoManager\Bundle\JsonParser;
use Symfony\Component\Finder\SplFileInfo;

class JsonParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JsonParser
     */
    private $parser;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->parser = new JsonParser();
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf('Contao\ManagerBundle\ContaoManager\Bundle\JsonParser', $this->parser);
        $this->assertInstanceOf('Contao\ManagerBundle\ContaoManager\Bundle\ParserInterface', $this->parser);
    }

    public function testSupports()
    {
        $this->assertTrue($this->parser->supports('foobar.json', 'json'));
        $this->assertTrue($this->parser->supports('foobar.json', null));
        $this->assertFalse($this->parser->supports([]));
        $this->assertFalse($this->parser->supports('foobar'));
    }

    public function testParseSimpleObject()
    {
        $configs = $this->parser->parse(__DIR__ . '/../../Fixtures/ContaoManager/Bundle/JsonParser/simple-object.json');

        $this->assertCount(1, $configs);

        /** @var ConfigInterface $config */
        $config = reset($configs);

        $this->assertInstanceOf('Contao\ManagerBundle\ContaoManager\Bundle\ConfigInterface', $config);
        $this->assertEquals('Contao\CoreBundle\ContaoCoreBundle', $config->getName());
        $this->assertEquals([], $config->getReplace());
        $this->assertTrue($config->loadInProduction());
        $this->assertTrue($config->loadInDevelopment());
        $this->assertEquals([], $config->getLoadAfter());
    }

    public function testParseSimpleString()
    {
        $configs = $this->parser->parse(__DIR__ . '/../../Fixtures/ContaoManager/Bundle/JsonParser/simple-string.json');

        $this->assertCount(1, $configs);

        /** @var ConfigInterface $config */
        $config = reset($configs);

        $this->assertInstanceOf('Contao\ManagerBundle\ContaoManager\Bundle\ConfigInterface', $config);
        $this->assertEquals('Contao\CoreBundle\ContaoCoreBundle', $config->getName());
        $this->assertEquals([], $config->getReplace());
        $this->assertTrue($config->loadInProduction());
        $this->assertTrue($config->loadInDevelopment());
        $this->assertEquals([], $config->getLoadAfter());
    }

    public function testParseDevelopmentAndProduction()
    {
        /** @var ConfigInterface[] $configs */
        $configs = $this->parser->parse(__DIR__ . '/../../Fixtures/ContaoManager/Bundle/JsonParser/dev-prod.json');

        $this->assertCount(3, $configs);

        $this->assertInstanceOf('Contao\ManagerBundle\ContaoManager\Bundle\ConfigInterface', $configs[0]);
        $this->assertTrue($configs[0]->loadInProduction());
        $this->assertTrue($configs[0]->loadInDevelopment());

        $this->assertInstanceOf('Contao\ManagerBundle\ContaoManager\Bundle\ConfigInterface', $configs[1]);
        $this->assertFalse($configs[1]->loadInProduction());
        $this->assertTrue($configs[1]->loadInDevelopment());

        $this->assertInstanceOf('Contao\ManagerBundle\ContaoManager\Bundle\ConfigInterface', $configs[2]);
        $this->assertTrue($configs[2]->loadInProduction());
        $this->assertFalse($configs[2]->loadInDevelopment());
    }

    public function testParseOptional()
    {
        /** @var ConfigInterface[] $configs */
        $configs = $this->parser->parse(__DIR__ . '/../../Fixtures/ContaoManager/Bundle/JsonParser/optional.json');

        $this->assertCount(2, $configs);

        $this->assertInstanceOf('Contao\ManagerBundle\ContaoManager\Bundle\ConfigInterface', $configs[1]);
        $this->assertEquals('Contao\CoreBundle\ContaoCoreBundle', $configs[1]->getName());
        $this->assertEquals(['core'], $configs[1]->getReplace());
        $this->assertTrue($configs[1]->loadInProduction());
        $this->assertTrue($configs[1]->loadInDevelopment());
        $this->assertEquals(['Foo\BarBundle\FooBarBundle'], $configs[1]->getLoadAfter());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testParseNoBundle()
    {
        $this->parser->parse(__DIR__ . '/../../Fixtures/ContaoManager/Bundle/JsonParser/no-bundle.json');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testParseMissingFile()
    {
        $this->parser->parse(__DIR__ . '/../../Fixtures/ContaoManager/Bundle/JsonParser/missing.json');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testParseInvalidJson()
    {
        $this->parser->parse(__DIR__ . '/../../Fixtures/ContaoManager/Bundle/JsonParser/invalid.json');
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testWillThrowExceptionIfFileNotExists()
    {
        $file = new SplFileInfo('iDoNotExist', 'relativePath', 'relativePathName');

        $this->parser->parse($file);
    }
}
