<?php

namespace uuf6429\SOJobMap;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Application
{
    /**
     * @var Service\Config
     */
    protected $config;

    /**
     * @var Service\FeedReader
     */
    protected $feedReader;

    /**
     * @var Service\MainView
     */
    protected $mainView;

    public function __construct(Service\Config $config, Service\FeedReader $feederReader, Service\MainView $mainView)
    {
        $this->config = $config;
        $this->feedReader = $feederReader;
        $this->mainView = $mainView;
    }

    public function run(Request $request = null)
    {
        try {
            if (!$request) {
                $request = Request::createFromGlobals();
            }

            $actionName = $request->query->get('action', 'index');
            $actionName = str_replace(' ', '', ucwords(str_replace('-', ' ', $actionName)));
            $actionType = strtolower($request->getMethod());

            $actionFn = $actionType . $actionName . 'Action';
            if (!method_exists($this, $actionFn)) {
                throw new Exception\HttpException("Action $actionFn not found.", Response::HTTP_NOT_FOUND);
            }

            $response = $this->$actionFn($request);
        } catch (\Throwable $ex) {
            error_log($ex);
            $response = new Response(
                \get_class($ex) . ': ' . $ex->getMessage(),
                $ex->getCode() ?: 500
            );
        }

        $response->send();
    }

    public function getIndexAction(Request $request): Response
    {
        return new StreamedResponse([$this->mainView, 'render']);
    }

    public function getMarkersAction(Request $request): Response
    {
        $records = $this->feedReader->read(
            trim($request->query->get('search', ''))
        );

        return new StreamedResponse(
            function () use ($records) {
                while (ob_get_level()) {
                    ob_end_flush();
                }
                ob_implicit_flush(true);

                foreach ($records as $record) {
                    echo \json_encode($record, JsonResponse::DEFAULT_ENCODING_OPTIONS) . "\n\n";

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        throw new \InvalidArgumentException(json_last_error_msg());
                    }
                }
            },
            StreamedResponse::HTTP_OK,
            [
                'Content-Type' => 'application/json',
                'X-Total-Count' => \count($records),
                'X-Accel-Buffering' => 'no',
            ]
        );
    }
}
