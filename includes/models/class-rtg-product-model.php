<?php
/**
 * 2014-2019 Retargeting BIZ SRL
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@retargeting.biz so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Retargeting SRL <info@retargeting.biz>
 * @copyright 2014-2019 Retargeting SRL
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Class WooCommerceRTGCategoryModel
 */
class WooCommerceRTGProductModel extends \RetargetingSDK\Product
{
    /**
     * @var bool
     */
    private $_hasProductData = false;

    /**
     * WooCommerceRTGProductModel constructor.
     * @param null $product
     * @throws Exception
     */
    public function __construct($product = null)
    {
        if (!$product instanceof WC_Product)
        {
            $productId = get_the_ID();

            if(!empty($productId))
            {
                $product = wc_get_product($productId);
            }
        }

        if ($product instanceof WC_Product && !empty($product->get_id()))
        {
            $this->_hasProductData = true;

            $this->_setProductData($product);
        }
    }

    /**
     * @param WC_Product $product
     * @throws Exception
     */
    private function _setProductData($product)
    {
        $this->setId($product->get_id());
        $this->setName($product->get_name());
        $this->setUrl($product->get_permalink());
        $this->_setProductPrices($product->get_regular_price(), $product->get_sale_price());
        $this->_setProductImages($product->get_image_id(), $product->get_gallery_image_ids());
        $this->_setProductCategories($product->get_category_ids());

        $this->setInventory([
            'variations' => false,
            'stock'      => $product->has_enough_stock(1)
        ]);
    }

    /**
     * @param $regularPrice
     * @param $salePrice
     * @throws Exception
     */
    private function _setProductPrices($regularPrice, $salePrice)
    {
        $this->setPrice($regularPrice);

        if($regularPrice != $salePrice)
        {
            $this->setPromo($salePrice);
        }
    }

    /**
     * @param $imageId
     * @param $galleryImageIds
     * @throws Exception
     */
    private function _setProductImages($imageId, $galleryImageIds)
    {
        $featureImageUrl = $this->_getProductImageURL($imageId);

        if (!empty($featureImageUrl))
        {
            $this->setImg($featureImageUrl);
        }

        if (is_array($galleryImageIds) && !empty($galleryImageIds))
        {
            $galleryImages = [];

            foreach ($galleryImageIds AS $galleryImageId)
            {
                $galleryImageUrl = $this->_getProductImageURL($galleryImageId);

                if (!empty($galleryImageUrl))
                {
                    $galleryImages[] = $galleryImageUrl;
                }
            }

            if (!empty($galleryImages))
            {
                $this->setAdditionalImages($galleryImages);
            }
        }
    }

    /**
     * @param $imageId
     * @return mixed|null
     */
    private function _getProductImageURL($imageId)
    {
        if (!empty($imageId))
        {
            $image = wp_get_attachment_image_src($imageId, 'medium_large');

            if (is_array($image) && !empty($image['0']))
            {
                return $image[0];
            }
        }

        return null;
    }

    /**
     * @param $categoryIds
     * @throws Exception
     */
    private function _setProductCategories($categoryIds)
    {
        if (is_array($categoryIds) && !empty($categoryIds))
        {
            $categories = [];

            foreach ($categoryIds AS $categoryId)
            {
                $RTGCategory = new WooCommerceRTGCategoryModel($categoryId);

                if ($RTGCategory->_hasCategoryData())
                {
                    $categories[] = $RTGCategory->getData(false);
                }
            }

            if (!empty($categories))
            {
                $this->setCategory($categories);
            }
        }
    }

    /**
     * @return bool
     */
    public function _hasProductData()
    {
        return $this->_hasProductData;
    }
}