<?php
/**
 * @copyright Copyright (c) 2017 Shopify Inc.
 * @license MIT
 */

namespace Shopify;

require 'mock/MockRequest.php';

class ShopifyVariantTest extends \PHPUnit_Framework_TestCase
{
    private $mockClient;

    public function setUp()
    {
        $this->mockClient = $this->getMockBuilder('Shopify\ShopifyClient')
            ->setConstructorArgs(['abc', '040350450399894.myshopify.com'])
            ->getMock();
    }

    public function testReadList()
    {
        $this->mockClient->expects($this->once())
            ->method('call')
            ->with('GET', 'products/5366123269/variants', null, ['fields' => 'product_id']);
        $this->mockClient->variants->readList(5366123269, ['fields' => 'product_id']);
    }

    public function testCreate()
    {
        $this->mockClient->expects($this->once())
            ->method('call')
            ->with('POST', 'products/235/variants', ['variant' => ['title' => 'cool product']]);
        $this->mockClient->variants->create(235, ['title' => 'cool product']);
    }

    public function testCreateTwo()
    {
        $client = new ShopifyClient('afnjeefewlkfj3bc', '040350450399894.myshopify.com');
        $client->setHttpClient(new MockRequest());
        $out = $client->variants->create(235, ['title' => 'cool product']);
        $this->assertEquals($out[0], "POST");
        $this->assertEquals($out[1], "https://040350450399894.myshopify.com/admin/products/235/variants.json");
        $this->assertEquals($out[3], ['variant' => ['title' => 'cool product']]);
    }

    public function testRead()
    {
        $this->mockClient->expects($this->once())->method('call')->with('GET', 'variants/1234');
        $this->mockClient->variants->read(1234);
    }

    public function testUpdate()
    {
        $this->mockClient->expects($this->once())->method('call')->with('PUT', 'variants/1234');
        $this->mockClient->variants->update(1234, ['variant' => ['sku' => 'josh']]);
    }

    public function testDestroy()
    {
        $this->mockClient->expects($this->once())->method('call')->with('DELETE', 'variants/1234');
        $this->mockClient->variants->destroy(1234);
    }

    public function testReadCount()
    {
        $this->mockClient->expects($this->once())->method('call')->with('GET', 'products/4324/variants/count');
        $this->mockClient->variants->readCount(4324);
    }

    public function testReadCountWithoutProductFilter()
    {
        $this->mockClient->expects($this->once())->method('call')->with('GET', 'variants/count');
        $this->mockClient->variants->readCount();
    }
}
