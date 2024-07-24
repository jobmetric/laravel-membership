<?php

namespace JobMetric\Membership\Tests;

use App\Models\Order;
use App\Models\Product;
use JobMetric\Layout\Exceptions\CollectionPropertyNotExistException;
use JobMetric\Layout\Tests\BaseLayout;
use Throwable;

class MemberTraitHasMemberTest extends BaseLayout
{
    public function test_check_has_member_trait()
    {
        $order = new Order;
        $this->assertIsArray($order->allowMemberCollection());
    }

    public function test_layout_trait_relationship()
    {
        $product = new Product;
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $product->layout());
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class, $product->layoutRelatable());
    }

    /**
     * @throws Throwable
     */
    public function test_layout_get_collection_value()
    {
        try {
            $product = new Product;
            $this->assertNull($product->layoutGetCollectionValue());
        } catch (Throwable $e) {
            $this->assertInstanceOf(CollectionPropertyNotExistException::class, $e);
        }

        $product = $this->addProduct();

        $this->assertNull($product->layoutGetCollectionValue());
    }

    /**
     * @throws Throwable
     */
    public function test_store()
    {
        $layout = $this->addLayout();
        $product = $this->addProduct();

        $product->storeLayout($layout['data']->id, 'admin', $product->layoutGetCollectionValue());

        $this->assertDatabaseHas('layout_relations', [
            'relatable_id' => $product->id,
            'relatable_type' => Product::class,
            'layout_id' => $layout['data']->id,
            'application' => 'admin',
            'collection' => $product->layoutGetCollectionValue(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function test_with_layout_relatable(): void
    {
        $layout = $this->addLayout();
        $product = $this->addProduct();

        $product->storeLayout($layout['data']->id, 'admin', $product->layoutGetCollectionValue());

        /**
         * @var Product $result
         */
        $result = Product::query()->where('id', $product->id)->first();
        $this->assertArrayNotHasKey('layoutRelatable', $result->getRelations());

        $result = Product::query()->where('id', $product->id)->first()->withLayoutRelatable();
        $this->assertArrayHasKey('layoutRelatable', $result->getRelations());

        $result = Product::query()->where('id', $product->id)->first()->withLayoutRelatable('admin');
        $this->assertArrayHasKey('layoutRelatable', $result->getRelations());

        $result = Product::query()->where('id', $product->id)->first()->withLayoutRelatable('admin', '1');
        $this->assertArrayHasKey('layoutRelatable', $result->getRelations());
    }

    /**
     * @throws Throwable
     */
    public function test_get_layout(): void
    {
        $layout = $this->addLayout();
        $product = $this->addProduct();

        $product->storeLayout($layout['data']->id, 'admin', $product->layoutGetCollectionValue());

        $result = Product::query()->where('id', $product->id)->first()->getLayout('admin', $product->layoutGetCollectionValue());
        $this->assertEquals($layout['data']->id, $result->id);

        $product->forgetLayout('admin', $product->layoutGetCollectionValue());

        $result = Product::query()->where('id', $product->id)->first()->getLayout('admin', $product->layoutGetCollectionValue());
        $this->assertNull($result);
    }

    /**
     * @throws Throwable
     */
    public function test_forget(): void
    {
        $layout = $this->addLayout();
        $product = $this->addProduct();

        $product->storeLayout($layout['data']->id, 'admin', $product->layoutGetCollectionValue());

        $product->forgetLayout('admin', $product->layoutGetCollectionValue());

        $this->assertDatabaseMissing('layout_relations', [
            'relatable_id' => $product->id,
            'relatable_type' => Product::class,
            'layout_id' => $layout['data']->id,
            'application' => 'admin',
            'collection' => $product->layoutGetCollectionValue(),
        ]);
    }
}
