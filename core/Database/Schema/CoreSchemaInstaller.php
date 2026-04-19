<?php

declare(strict_types=1);

namespace ZMosquita\Core\Database\Schema;

use Throwable;
use ZMosquita\Core\Database\Connection;
use ZMosquita\Core\Database\DataDefResolver;

final class CoreSchemaInstaller
{
    public function __construct(
        private Connection $db,
        private DataDefResolver $dataDefResolver,
        private SqlSchemaLoader $loader
    ) {
    }

    public function install(bool $continueOnError = false): SchemaInstallResult
    {
        $files = $this->files();
        $executedStatements = [];
        $errors = [];

        if ($files === []) {
            return SchemaInstallResult::success([]);
        }

        $this->db->beginTransaction();

        try {
            foreach ($files as $file) {
                foreach ($this->loader->loadStatements($file) as $statement) {
                    try {
                        $this->db->execute($statement);
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
                $this->db->rollBack();
                return SchemaInstallResult::failure($files, $executedStatements, $errors);
            }

            if ($errors !== [] && $continueOnError) {
                $this->db->commit();
                return SchemaInstallResult::failure($files, $executedStatements, $errors);
            }

            $this->db->commit();

            return SchemaInstallResult::success($files, $executedStatements);
        } catch (Throwable $e) {
            $this->db->rollBack();

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

    /**
     * @return string[]
     */
    public function files(): array
    {
        return $this->dataDefResolver->allCore();
    }

    public function installFile(string $path): SchemaInstallResult
    {
        $executedStatements = [];
        $errors = [];

        $this->db->beginTransaction();

        try {
            foreach ($this->loader->loadStatements($path) as $statement) {
                $this->db->execute($statement);
                $executedStatements[] = $statement;
            }

            $this->db->commit();

            return SchemaInstallResult::success([$path], $executedStatements);
        } catch (Throwable $e) {
            $this->db->rollBack();

            $errors[] = [
                'file' => $path,
                'statement' => $statement ?? '',
                'error' => $e->getMessage(),
            ];

            return SchemaInstallResult::failure([$path], $executedStatements, $errors);
        }
    }
}