<?php
class ScrapBeastImport
{
    public $sourceUrl;
    public $importStep = 0;

    public function setSourceUrl($url)
    {
        $this->sourceUrl = $url;
    }

    public function setImportStep($step)
    {
        $this->importStep = $step;
    }

    public function start()
    {
        $products = $this->_getSourceProducts();
        if (empty($products)) {
            return;
        }

        if (!isset($products[$this->importStep])) {
            return array('done'=>true, 'message'=>'All products are imported.');
        }

        return $this->_saveProductToDatabase($products[$this->importStep]);
    }

    private function _saveProductToDatabase($product) {

        $log = '';

        $urlManager = new \Microweber\Providers\UrlManager();
        $productUrl = $urlManager->slug($product['title']);

        $productDbId = false;
        $findProduct = get_content('single=1&url=' . $productUrl);
        if ($findProduct) {
            $productDbId = $findProduct['id'];
        }

        $readyContent = array();
        if ($productDbId) {
            $readyContent['id'] = $productDbId;
        } else {
            // Download images
        }

        // Download images
        /*if (!is_dir( media_uploads_path() .'scrapbeast')) {
            mkdir_recursive( media_uploads_path() .'scrapbeast');
        }*/

        $downloadedImages = [];
        foreach ($product['images'] as $image) {
            $targetImageFile = media_uploads_path() . md5($image['url']) . '.jpg';
            if (is_file($targetImageFile)) {
                $downloadedImages[] = $targetImageFile;
                continue;
            }
            $log .= 'Downloading product image..<br />';
            $imageContent = file_get_contents($image['url']);
            if ($imageContent) {
                $log .= 'Save product image..<br />';
                $saveImage = file_put_contents($targetImageFile, $imageContent);
                if ($saveImage) {
                    $downloadedImages[] = media_uploads_url() . md5($image['url']) . '.jpg';
                }
            }
        }

        if (!empty($downloadedImages)) {
            $images = implode(', ', $downloadedImages);
            $readyContent['images'] = $images;
        }

        $readyContent['title'] = $product['title'];

        $readyContent['content'] = '';
        $readyContent['content_type'] = 'product';
        $readyContent['subtype'] = 'product';
        $readyContent['is_active'] = 1;

        $readyContent['url'] = $productUrl;

       //$tags = implode(', ', $tags);

        //$readyContent['tags'] = $tags;

        // $categories = implode(', ', $categories);
        //   $readyContent['categories'] = $categories;

        $readyContent['custom_field_price'] = $product['price'];

        if ($product['stock']) {
            $readyContent['data_qty'] = 3;
        } else {
            $readyContent['data_qty'] = 0;
        }

        $readyContent['data_sku'] = $product['remote_id'];
        $readyContent['custom_fields'] = array(
            //  array('type' => 'dropdown', 'name' => 'Color', 'value' => array('Purple', 'Blue')),
        );

        $save = save_content($readyContent);
        if ($save) {
            $log .= 'Product <b>'  . $product['title'] . '</b> are saved.';
        }

        return array('success'=>true, 'message'=>$log);
    }

    private function _getSourceProducts()
    {
        $data = file_get_contents($this->sourceUrl);
        return json_decode($data, true);
    }
}

