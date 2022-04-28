<?php

namespace Mikkimike\Exchange1C\Services;

use Mikkimike\Exchange1C\Events\ImportLog;
use Mikkimike\Exchange1C\PayloadTypes\BatchStart;
use Mikkimike\Exchange1C\PayloadTypes\RelationProducts;

class RelationService
{
    public function import()
    {
        $filename = basename($this->request->get('filename'));
        $this->_ids = [];
        $category = false;
        if ($this->request->has('category')) {
            $category = $this->request->get('category');
        }
        $this->dispatcher->dispatch(new ImportLog('Sync users'));
        $xml = simplexml_load_string(file_get_contents(storage_path('app/1c_exchange/' . $category . '/' . $filename)));

        foreach ($xml as $item) {
            $this->ImportProcessDataBridge(new RelationProducts($item));
        }

        $this->ImportProcessDataBridge(new BatchStart("RELATION PRODUCTS IMPORT"));

    }
}