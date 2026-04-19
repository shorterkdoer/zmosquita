<?php

declare(strict_types=1);

namespace ZMosquita\Core\Database\Schema;

use Throwable;
use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\InitialSeedsResolver;

final class InitialSeedsInstaller
{
    public function __construct(
        private Connection $connection,
        private InitialSeedsResolver $resolver,
        private SqlSchemaLoader $loader
    ) {
    }

    public function installCore(bool $continueOnError = false): SchemaInstallResult
    {
        return $this->installSeeds($this->resolver->allCore(), $continueOnError);
    }

    public function installApp(string $appCode, bool $continueOnError = false): SchemaInstallResult
    {
        if (!$this->resolver->hasAppSeeds($appCode)) {
            return SchemaInstallResult::success([]);
        }

        return $this->installSeeds($this->resolver->allApp($appCode), $continueOnError);
    }

    public function installFile(string $appCode, string $filename, bool $continueOnError = false): SchemaInstallResult
    {
        $path = $this->resolver->app($appCode, $filename);

        return $this->installSeeds([$path], $continueOnError);
    }

    private function installSeeds(array $files, bool $continueOnError): SchemaInstallResult
    {
        if ($files === []) {
            return SchemaInstallResult::success([]);
        }

        $executedStatements = [];
        $errors = [];

        $this->connection->beginTransaction();

        try {
            foreach ($files as $file) {
                foreach ($this->loader->loadStatements($file) as $statement) {
                    try {
                        $this->connection->execute($statement);
                        $executedStatements[] = $statement;
                    } catch (Throwable $e) {
                        $errors[] = [
                            'file' => $file,
                            'statement' => $statement,
                            'error' => $e->getMessage(),
                        ];

                        if (!$continueOnError) {
                            throw $e;
                        }
                    }
                }
            }

            if ($errors !== [] && !$continueOnError) {
                $this->connection->rollBack();
                return SchemaInstallResult::failure($files, $executedStatements, $errors);
            }

            if ($errors !== [] && $continueOnError) {
                $this->connection->commit();
                return SchemaInstallResult::failure($files, $executedStatements, $errors);
            }

            $this->connection->commit();

            return SchemaInstallResult::success($files, $executedStatements);
        } catch (Throwable $e) {
            $this->connection->rollBack();

            if ($errors === []) {
                $errors[] = [
                    'file' => '',
                    'statement' => '',
                    'error' => $e->getMessage(),
                ];
            }

            return SchemaInstallResult::failure($files, $executedStatements, $errors);
        }
    }
}
