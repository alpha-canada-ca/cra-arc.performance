
select distinct proj_ids.id,
--                 "UX Research Project Title" as title,
                num_tests_delayed,
                num_tests_in_progress,
                num_tests_complete,
                case when num_tests_delayed > 0 then 'Delayed'
                     when num_tests_in_progress > 0 then 'In Progress'
                     when num_tests_complete > 0 then 'Complete'
                     else 'Unknown' end as project_status
from ux_tests left join (

--     delayed
    select d_proj_title, num_tests_delayed from (

            select "UX Research Project Title" as d_proj_title,
                   Status,
                   count(Status) as num_tests_delayed
            from ux_tests
            where Status == 'Delayed'
            group by d_proj_title, Status

    ) as delayed

) on "UX Research Project Title" = d_proj_title left join (
--      in progress

    select i_proj_title, num_tests_in_progress from (

            select "UX Research Project Title" as i_proj_title,
                   Status,
                   count(Status) as num_tests_in_progress
            from ux_tests
            where Status == 'In Progress'
            group by i_proj_title, Status

    ) as in_progress

) on "UX Research Project Title" = i_proj_title left join (
--         completed

    select p_proj_title, num_tests_complete from (

        select "UX Research Project Title" as p_proj_title,
               Status,
               count(Status) as num_tests_complete
        from ux_tests
        where Status == 'Complete'
        group by p_proj_title, Status) as completed

) on "UX Research Project Title" = p_proj_title left join (
--         join to get project ids

    select id, title from projects

) as proj_ids on "UX Research Project Title" = proj_ids.title