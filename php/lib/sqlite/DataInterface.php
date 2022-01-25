<?php

include_once "php/lib/sqlite/Driver.php";
include_once "php/lib/sqlite/Query.php";
include_once "includes/functions.php";

use Builder\Query;
use Driver\Sqlite;


// todo: getByProjectName methods (because the projects table should really be called "ux_tests" or something)

class DataInterface
{
    private Sqlite $sqlite;
    private Query $queryBuilder;

    public function __construct($dbPath = 'php/lib/sqlite/upd-db.sqlite') {
        $this->sqlite = new Sqlite($dbPath);
        $this->queryBuilder = $this->sqlite->getQueryBuilder();
    }


    public function getDb(): Sqlite
    {
        return $this->sqlite;
    }

    /**
     * @return Query
     */
    public function getQueryBuilder(): Query
    {
        return $this->queryBuilder;
    }

    /**
     * @param $tableName
     * @param array $selectedFields Optional array of field names to SELECT
     * @param array $where Optional SQL WHERE clauses - can be a string, array, or 2D array w/ multiple clauses
     * @return array Array containing an associative array for each row (key = column name)
     */
    public function selectFromTable($tableName, $selectedFields = [], $where = []): array
    {
        $fields = '*';

        if (!is_array($selectedFields)) {
            $selectedFields = [$selectedFields];
        }

        if (count($selectedFields) > 0) {
            $fields = implode(',', $selectedFields);
        }

        $query = $this->queryBuilder->select($fields);

        if (count($where) > 0) {
            if (!is_array($where[0])) {
                $query = $query->where(...$where);
            } else {
                $query = $query->where(...$where[0]);

                foreach (array_slice($where, 1) as $whereClause) {
                    $query = $query->orWhere(...$whereClause);
                }
            }
        }

        $query = $query->get($tableName);

        $results = [];

        while ($row = $query->fetchArray(1)) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function executeQuery($query): array
    {
        $results = [];

        while ($row = $query->fetchArray(1)) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function listTables(): array
    {
        $tblNames = $this->selectFromTable('sqlite_master', 'tbl_name');

        return array_reduce($tblNames, function ($finalArray, $item) {
            return [...$finalArray, ...array_values($item)];
        }, []);
    }

    public function getTableInfo($tblName): array {
        $where = ['tbl_name', '=', $tblName];
        $tableCreationSql = $this->selectFromTable('sqlite_master', 'sql', $where);

        return array_map(function($item) {
            return $item['sql'];
        }, $tableCreationSql);
    }

    public function getProjects($selectedFields = []): array
    {
        return $this->selectFromTable('projects', $selectedFields);
    }

    public function getProjectById(string $projectId, $selectedFields = []): array
    {
        if (count($selectedFields) > 0) {
            $fields = 'id,' . implode(',', $selectedFields);
        } else {;
            $fields = '*';
        }

        $query = $this->queryBuilder
            ->select($fields)
            ->where('id', '=', $projectId)
            ->get('projects');

        $results = [];

        while ($row = $query->fetchArray(1)) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function getProjectsByTaskId($taskId, $selectedFields = []): array
    {
        $fields = '';

        if (count($selectedFields) > 0) {
            $fields = ',' . implode(',', $selectedFields);
        }

        $query = $this->queryBuilder
            ->select('id' . $fields)
            ->where('task_id', '=', $taskId)
            ->join('tasks_projects', 'id = tasks_projects.project_id')
        ->get('projects');

        $results = [];

        while ($row = $query->fetchArray(1)) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function getProjectsByPageId($pageId, $selectedFields = []): array
    {
        $fields = '';

        if (count($selectedFields) > 0) {
            $fields = ',' . implode(',', $selectedFields);
        }

        $query = $this->queryBuilder
            ->select('id' . $fields)
            ->where('page_id', '=', $pageId)
            ->join('pages_projects', 'id = pages_projects.project_id')
            ->get('projects');

        $results = [];

        while ($row = $query->fetchArray(1)) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function getTasks($selectedFields = []): array
    {
        return $this->selectFromTable('tasks', $selectedFields);
    }


    public function getTaskById(string $taskId, $selectedFields = []): array
    {
        if (count($selectedFields) > 0) {
            $fields = 'id,' . implode(',', $selectedFields);
        } else {;
            $fields = '*';
        }

        $query = $this->queryBuilder
            ->select($fields)
            ->where('id', '=', $taskId)
            ->get('tasks');

        return $this->executeQuery($query);
    }

    public function getTasksByProjectId($projectId, $selectedFields = []): array
    {
        $fields = '';

        if (count($selectedFields) > 0) {
            $fields = ',' . implode(',', $selectedFields);
        }

        $query = $this->queryBuilder
            ->select('id' . $fields)
            ->where('project_id', '=', $projectId)
            ->join('tasks_projects', 'id = tasks_projects.task_id')
            ->get('tasks');

        $results = [];

        while ($row = $query->fetchArray(1)) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function getTasksByPageId($pageId, $selectedFields = []): array
    {
        $fields = '';

        if (count($selectedFields) > 0) {
            $fields = ',' . implode(',', $selectedFields);
        }

        $query = $this->queryBuilder
            ->select('id' . $fields)
            ->where('page_id', '=', $pageId)
            ->join('tasks_pages', 'id = tasks_pages.task_id')
            ->get('tasks');

        $results = [];

        while ($row = $query->fetchArray(1)) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }


    public function getPages($selectedFields = []): array
    {
        return $this->selectFromTable('pages', $selectedFields);
    }

    public function getPagesByProjectId($projectId, $selectedFields = []): array
    {
        $fields = '';

        if (count($selectedFields) > 0) {
            $fields = ',' . implode(',', $selectedFields);
        }

        $query = $this->queryBuilder
            ->select('id' . $fields)
            ->where('project_id', '=', $projectId)
            ->join('pages_projects', 'id = pages_projects.page_id')
            ->get('pages');

        $results = [];

        while ($row = $query->fetchArray(1)) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function getPagesByTaskId($taskId, $selectedFields = []): array
    {
        $fields = '';

        if (count($selectedFields) > 0) {
            $fields = ',' . implode(',', $selectedFields);
        }

        $query = $this->queryBuilder
            ->select('id' . $fields)
            ->where('task_id', '=', $taskId)
            ->join('tasks_pages', 'id = tasks_pages.page_id')
            ->get('pages');

        $results = [];

        while ($row = $query->fetchArray(1)) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function getUxTests($selectedFields = []): array
    {
        return $this->selectFromTable('ux_tests', $selectedFields);
    }

    public function getUxTestsByProjectId($projectId, $selectedFields = []): array
    {
        $fields = '';

        if (count($selectedFields) > 0) {
            $fields = ',' . implode(',', $selectedFields);
        }

        $query = $this->queryBuilder
            ->select('id' . $fields)
            ->where('project_id', '=', $projectId)
            ->join('tests_projects', 'id = tests_projects.test_id')
            ->get('ux_tests');

        $results = [];

        while ($row = $query->fetchArray(1)) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function getUxTestsByTaskId($taskId, $selectedFields = []): array
    {
        $fields = '';

        if (count($selectedFields) > 0) {
            $fields = ',' . implode(',', $selectedFields);
        }

        $query = $this->queryBuilder
            ->select('id' . $fields)
            ->where('task_id', '=', $taskId)
            ->join('tasks_tests', 'id = tasks_tests.test_id')
            ->get('ux_tests');

        $results = [];

        while ($row = $query->fetchArray(1)) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }

    public function getTaskByUxTestId($testId, $selectedFields = []): array
    {
        $fields = '';

        if (count($selectedFields) > 0) {
            $fields = ',' . implode(',', $selectedFields);
        }

        $query = $this->queryBuilder
            ->select('id' . $fields)
            ->where('test_id', '=', $testId)
            ->join('tasks_tests', 'id = tasks_tests.task_id')
            ->get('tasks');

        $results = [];

        while ($row = $query->fetchArray(1)) {
            if (is_array($row)) {
                $results[] = $row;
            }
        }

        return $results;
    }
}
