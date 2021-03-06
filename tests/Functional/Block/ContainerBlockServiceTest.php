<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\BlockBundle\Tests\Functional\Block;

use Doctrine\ODM\PHPCR\ChildrenCollection;
use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Block\BlockRendererInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Cmf\Bundle\BlockBundle\Block\ContainerBlockService;
use Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\ContainerBlock;
use Symfony\Cmf\Bundle\BlockBundle\Doctrine\Phpcr\SimpleBlock;
use Symfony\Component\HttpFoundation\Response;

class ContainerBlockServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testExecutionOfDisabledBlock()
    {
        $containerBlock = new ContainerBlock();
        $containerBlock->setEnabled(false);

        $blockRendererMock = $this->createMock(BlockRendererInterface::class);
        $blockRendererMock->expects($this->never())
            ->method('render');

        $templatingMock = $this->createMock(EngineInterface::class);

        $containerBlockService = new ContainerBlockService('test-service', $templatingMock, $blockRendererMock);
        $containerBlockService->execute(new BlockContext($containerBlock));
    }

    public function testExecutionOfEnabledBlock()
    {
        $template = 'CmfBlockBundle:Block:block_container.html.twig';

        $simpleBlock1 = new SimpleBlock();
        $simpleBlock1->setId(1);

        $simpleBlock2 = new SimpleBlock();
        $simpleBlock2->setId(2);

        $childrenCollectionMock = $this->createMock(ChildrenCollection::class);

        $containerBlock = new ContainerBlock('foo');
        $containerBlock->setEnabled(true);
        $containerBlock->setChildren($childrenCollectionMock);

        $settings = ['divisible_by' => 0, 'divisible_class' => '', 'child_class' => '', 'template' => $template];

        $blockContext = new BlockContext($containerBlock, $settings);

        $responseContent1 = 'Rendered Simple Block 1.';
        $responseContent2 = 'Rendered Simple Block 2.';

        $blockRendererMock = $this->createMock(BlockRendererInterface::class);

        $templatingMock = $this->createMock(EngineInterface::class);

        $templatingMock
            ->expects($this->once())
            ->method('renderResponse')
            ->with(
                $this->equalTo($template),
                $this->equalTo([
                    'block' => $containerBlock,
                    'settings' => $settings,
                ]),
                $this->isInstanceOf('Symfony\Component\HttpFoundation\Response')
            )
            ->will($this->returnValue(new Response($responseContent1.$responseContent2)))
        ;

        $containerBlockService = new ContainerBlockService('test-service', $templatingMock, $blockRendererMock);
        $response = $containerBlockService->execute($blockContext);
        $this->assertInstanceof('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(($responseContent1.$responseContent2), $response->getContent());
    }

    public function testExecutionOfBlockWithNoChildren()
    {
        $template = 'CmfBlockBundle:Block:block_container.html.twig';

        $childrenCollectionMock = $this->createMock(ChildrenCollection::class);

        $containerBlock = new ContainerBlock('foo');
        $containerBlock->setEnabled(true);
        $containerBlock->setChildren($childrenCollectionMock);

        $settings = ['divisibleBy' => 0, 'divisibleClass' => '', 'childClass' => '', 'template' => $template];

        $blockContext = new BlockContext($containerBlock, $settings);

        $blockRendererMock = $this->createMock(BlockRendererInterface::class);

        $templatingMock = $this->createMock(EngineInterface::class);

        $templatingMock
            ->expects($this->once())
            ->method('renderResponse')
            ->with(
                $this->equalTo($template),
                $this->equalTo([
                    'block' => $containerBlock,
                    'settings' => $settings,
                ]),
                $this->isInstanceOf('Symfony\Component\HttpFoundation\Response')
            )
            ->will($this->returnValue(new Response('')))
        ;

        $containerBlockService = new ContainerBlockService('test-service', $templatingMock, $blockRendererMock);
        $response = $containerBlockService->execute($blockContext);
        $this->assertInstanceof('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals('', $response->getContent());
    }
}
