<?php
/*
 * Various helper functions for data transformation
 * */

// Returns the average of the numbers in an array
function avg($array = []) {
    if (count($array) === 0) {
        return 0;
    }

    return array_sum($array) / count($array);
}


function getProjectsWithStatusCounts(DataInterface $db): array
{
    // Query should return the following columns
    // id: string - the project id
    // num_tests_delayed: int or null if 0 - the number of tests under that project with the "Delayed" Status
    // num_tests_in_progress: int or null if 0 - the number of tests under that project with the "In Progress" Status
    // num_tests_complete: int or null if 0 - the number of tests under that project with the "Complete" Status
    $project_status_counts_sql = file_get_contents('./php/lib/sqlite/sql/project-ux_test_status_counts.sql');

    $avgSuccessRateSql = "
        select \"UX Research Project Title\" as project_title, round(avg(\"Success Rate\") * 100, 2) as avg_success_rate
        from ux_tests
        group by project_title
    ";

    $launchDateSql = "
        select \"UX Research Project Title\" as project_title, date(max(\"Launch Date\")) as \"Launch Date\"
        from ux_tests
        group by project_title
    ";

    // now we join it to the projects table using the ids
    $projects_query = $db->getQueryBuilder()
        ->select()
        ->join("($project_status_counts_sql) as proj_status", 'projects.id == proj_status.id')
        ->join("($avgSuccessRateSql) as avg_success_rate", 'projects.title == avg_success_rate.project_title')
        ->join("($launchDateSql) as launch_date", 'projects.title == launch_date.project_title')
        ->orderBy('projects.title')
        ->get('projects');

    $projectsData = $db->executeQuery($projects_query);

    return $projectsData;
}

// Takes a variable number of functions as arguments and returns a single function that combines them all
function compose(callable ...$fns): Closure
{
    return fn($arg) => array_reduce($fns, fn($result, $nextFn) => $nextFn($result), $arg);
}

// We use these as building blocks with the compose function to build functions that query and transform the data object the way we want
function makeSelectCol(string $colName): Closure
{
    return fn($data) => array_column($data, $colName);
}

function makeFilter(callable $filterFn): Closure
{
    return fn($data) => array_filter($data, $filterFn);
}

function makeMap(callable $mapFn): Closure
{
    return fn($data) => array_map($mapFn, $data);
}

function makeFilterByProjectId(string $projId): Closure
{
    return makeFilter(fn($row) => $row['project_id'] == $projId);
}