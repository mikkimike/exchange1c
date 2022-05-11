<?php

namespace Mikkimike\Exchange1C\Services;

use Mikkimike\Exchange1C\Config;
use Mikkimike\Exchange1C\Events\ImportLog;
use Mikkimike\Exchange1C\Interfaces\EventDispatcherInterface;
use Mikkimike\Exchange1C\Interfaces\ModelBuilderInterface;
use Mikkimike\Exchange1C\PayloadTypes\BatchStart;
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
        $this->dispatcher->dispatch(new ImportLog('Sync users'));
        $xml = simplexml_load_string(file_get_contents(storage_path('app/1c_exchange/' . $category . '/' . $filename)));

        foreach ($xml as $item) {
            $this->ImportProcessDataBridge(new RelationProducts($item));
        }

        $this->ImportProcessDataBridge(new BatchStart("RELATION PRODUCTS IMPORT"));

    }
}
