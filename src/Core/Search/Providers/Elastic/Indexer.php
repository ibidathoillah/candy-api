<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic;

use Carbon\Carbon;
use Elastica\Client;
use Elastica\Index;
use Elastica\Document;
use Elastica\Mapping;
use GetCandy\Api\Core\Languages\Services\LanguageService;
use GetCandy\Api\Core\Scopes\ChannelScope;
use GetCandy\Api\Core\Scopes\CustomerGroupScope;
use Illuminate\Database\Eloquent\Model;

class Indexer
{
    // use InteractsWithIndex;

    protected $batch = 0;

    /**
     * The language service instance.
     *
     * @var LanguageService
     */
    protected $lang;

    /**
     * The indice resolver.
     *
     * @var IndiceResolver
     */
    protected $resolver;

    public function __construct(LanguageService $lang, IndiceResolver $resolver)
    {
        $this->client = new Client(config('getcandy.search.client_config.elastic', []));
        $this->lang = $lang;
        $this->resolver = $resolver;
    }

    /**
     * Reindex a model.
     *
     * @param Model $model
     * @return void
     */
    public function reindex($model)
    {
        $type = $this->resolver->getType($model);

        $this->batch = 0;

        $languages = $this->lang->all();

        $index = $this->getIndexName($type);


        $suffix = now()->timestamp;

        $model = new $model;

        $aliases = [];

        foreach ($languages as $language) {
            $alias = $index.'_'.$language->lang;
            $this->createIndex($alias."_{$suffix}", $type);
            $aliases[$alias] = $alias."_{$suffix}";
        }


        $models = $model->withoutGlobalScopes([
            CustomerGroupScope::class,
            ChannelScope::class,
        ])->limit(1000)
            ->offset($this->batch)
            ->get();

        $type->setSuffix($suffix);

        $indices = $this->client->getStatus()->getIndexNames();

        while ($models->count()) {
            $indexes = [];
            foreach ($models as $model) {
                $indexables = $type->getIndexDocument($model);
                echo '.';
                foreach ($indexables as $indexable) {
                    $document = new Document(
                        $indexable->getId(),
                        $indexable->getData()
                    );
                    $indexes[$indexable->getIndex()][] = $document;
                }
            }

            foreach ($indexes as $key => $documents) {
                $index = $this->client->getIndex($key);
                $elasticaType = $index->getType($type->getHandle());
                $elasticaType->addDocuments($documents);
            }

            $elasticaType->addDocuments($documents);
            $elasticaType->getIndex()->refresh();

            echo ':batch:'.$this->batch;
            $this->batch += 1000;
            $models = $model->withoutGlobalScopes([
                CustomerGroupScope::class,
                ChannelScope::class,
            ])->limit(1000)->offset($this->batch)->get();
        }

        foreach ($aliases as $alias => $index) {
            $index = $this->client->getIndex($index);
            $index->addAlias($alias);

            $indices = $this->client->getStatus()->getIndicesWithAlias($alias);

            $currentTime = $this->getIndiceTime($index->getName());

            foreach ($indices as $indice) {
                $time = $this->getIndiceTime($indice->getName());
                if (! $time) {
                    $indice->delete();
                    continue;
                }
                if ($currentTime->gt($time)) {
                    $indice->delete();
                }
            }
        }
    }

    protected function getIndiceTime($name)
    {
        $fragments = explode('_', $name);
        try {
            return Carbon::createFromTimestamp(end($fragments));
        } catch (\ErrorException $e) {
        }
    }

    /**
     * Updates the mappings for the model.
     * @param  Elastica\Index $index
     * @return void
     */
    public function updateMappings(Index $index, $type)
    {
        return tap(new Mapping($type->getMapping()), function ($mapping) use ($index) {
            $mapping->send($index);
        });
    }

    /**
     * Gets a timestamped index.
     *
     * @param [type] $type
     * @return void
     */
    protected function getIndexName($type)
    {
        return config('getcandy.search.index_prefix', 'candy').
            '_'.
            $type->getHandle();
    }

    /**
     * Index a single object.
     *
     * @param Model $model
     * @return void
     */
    public function indexObject(Model $model)
    {
        $type = $this->resolver->getType($model);

        // Get our aliases
        $status = $this->client->getStatus();

        $index = $this->getIndexName($type);

        $langs = $this->lang->all();

        $indexables = $type->getIndexDocument($model);

        foreach ($langs as $lang) {
            $alias = $index.'_'.$lang->lang;

            $indices = $status->getIndicesWithAlias($alias);

            $documents = $indexables->filter(function ($doc) use ($alias) {
                return $doc->getIndex() == $alias;
            });

            foreach ($indices as $indice) {
                $elasticaType = $indice->getType($type->getHandle());

                foreach ($indexables as $indexable) {
                    $document = new Document(
                        $indexable->getId(),
                        $indexable->getData()
                    );
                    $elasticaType->addDocument($document);
                }
            }
        }

        return true;
    }

    public function indexObjects($models)
    {
        $status = $this->client->getStatus();
        $langs = $this->lang->all();

        $pending = [];

        foreach ($models as $model) {
            $type = $this->resolver->getType($model);
            $index = $this->getIndexName($type);

            $indexables = $type->getIndexDocument($model);

            foreach ($langs as $lang) {
                $alias = $index.'_'.$lang->lang;

                $indices = $status->getIndicesWithAlias($alias);

                $documents = $indexables->filter(function ($doc) use ($alias) {
                    return $doc->getIndex() == $alias;
                });

                foreach ($indices as $indice) {
                    $elasticaType = $indice->getType($type->getHandle());
                    $realIndex = $indice->getName();
                    if (empty($pending[$realIndex])) {
                        $pending[$realIndex]['docs'] = collect();
                        $pending[$realIndex]['type'] = $type;
                    }
                    foreach ($indexables as $indexable) {
                        $pending[$indice->getName()]['docs']->push(new Document(
                            $indexable->getId(),
                            $indexable->getData()
                        ));
                    }
                }
            }
        }

        foreach ($pending as $indexName => $data) {
            $index = $this->client->getIndex($indexName);
            $type = $index->getType($data['type']->getHandle());
            $type->addDocuments($data['docs']->toArray());
            $index->refresh();
        }
    }

    /**
     * Add a single model to the elastic index.
     *
     * @param Model $model
     * @param string $suffix
     * @return bool
     */
    protected function addToIndex(Model $model, $suffix = null)
    {
        $type = $this->type->setSuffix($suffix);

        $indexables = $type->getIndexDocument($model);

        foreach ($indexables as $indexable) {
            $index = $this->client->getIndex(
                $indexable->getIndex()
            );

            $elasticaType = $index->getType($this->type->getHandle());

            $document = new Document(
                $indexable->getId(),
                $indexable->getData()
            );

            $elasticaType->addDocument($document);
        }

        return true;
    }


    /**
     * Create an index based on the model.
     * @return void
     */
    public function createIndex($name, $type)
    {
        $index = $this->client->getIndex($name);
        $index->create([
            'settings' => [
                'analysis' => [
                    'analyzer' => [
                        'trigram' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => ['shingle'],
                        ],
                        'standard_lowercase' => [
                            'type' => 'custom',
                            'tokenizer' => 'standard',
                            'filter' => ['lowercase'],
                        ],
                        'candy' => [
                            'tokenizer' => 'standard',
                            'filter' => ['lowercase', 'stop', 'porter_stem'],
                        ],
                    ],
                    'filter' => [
                        'shingle' => [
                            'type' => 'shingle',
                            'min_shingle_size' => 2,
                            'max_shingle_size' => 3,
                        ],
                    ],
                ],
            ],
        ]);
        $this->updateMappings($index, $type);

        return $index;
    }
}
