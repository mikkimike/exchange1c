<?php

namespace Mikkimike\Exchange1C\Services;

use Mikkimike\Exchange1C\Config;
use Mikkimike\Exchange1C\Events\ImportLog;
use Mikkimike\Exchange1C\Events\ImportProcessDataBridge;
use Mikkimike\Exchange1C\Interfaces\EventDispatcherInterface;
use Mikkimike\Exchange1C\Interfaces\ModelBuilderInterface;
use Mikkimike\Exchange1C\PayloadTypes\BatchStart;
use Mikkimike\Exchange1C\PayloadTypes\PayloadTypeInterface;
use Mikkimike\Exchange1C\PayloadTypes\RelationProducts;
use Symfony\Component\HttpFoundation\Request;

class RelationService
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;
    /**
     * @var ModelBuilderInterface
     */
    private $modelBuilder;

    public function __construct(Request $request, Config $config, EventDispatcherInterface $dispatcher, ModelBuilderInterface $modelBuilder)
    {
        $this->request = $request;
        $this->config = $config;
        $this->dispatcher = $dispatcher;
        $this->modelBuilder = $modelBuilder;
    }

    public function import()
    {
        $filename = basename($this->request->get('filename'));
        $category = false;
        if ($this->request->has('category')) {
            $category = $this->request->get('category');
        }
        $this->dispatcher->dispatch(new ImportLog('Sync products relations'));
        $xml = simplexml_load_string(file_get_contents(storage_path('app/1c_exchange/' . $category . '/' . $filename)));

        $relations = [];
        foreach ($xml as $item) {
            $productId = (string) $item->attributes()->Номенклатура;
            $relatedProductId = (string) $item->СопутствующийТовар->attributes()->СопутствующийТовар;
            $relations[$productId][] = $relatedProductId;
        }

        $this->ImportProcessDataBridge(new RelationProducts($relations));

        $this->ImportProcessDataBridge(new BatchStart("RELATION PRODUCTS IMPORT"));

    }
    protected function ImportProcessDataBridge(PayloadTypeInterface $model): void
    {
        $event = new ImportProcessDataBridge($model);
        $this->dispatcher->dispatch($event);
    }
}
