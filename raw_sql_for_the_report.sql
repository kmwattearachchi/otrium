/* 7 days turnover per brand (Brand Name, Total turnover(excluding VAT) per day for last 7 days)*/
SET @sql = NULL;
SELECT GROUP_CONCAT(DISTINCT
                    CONCAT(
                            'sum(case when DATE_FORMAT(g.date, ''%Y-%m-%d 00:00:00'') = ''',
                            dt,
                            ''' then g.turnover else 0 end) AS `',
                            dt, '`'
                        )
           )
INTO @sql
from (
         select DATE_FORMAT(g.date, '%Y-%m-%d 00:00:00') as dt
         from gmv g
         where g.date between date('2018-05-01') and date('2018-05-07')
         order by g.date
     ) d;

SET @sql
    = CONCAT('SELECT b.name,
            sum(g.turnover) as turnover_per_brand_for_period,
            round(sum(g.turnover) / 1.21, 2) as vat_excluded,', @sql, '
            from brands b
            inner join gmv g
              on b.id = g.brand_id
           where g.date between date(''2018-05-01'') and date(''2018-05-07'')
            group by b.name');

select @sql;

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


/* 7 days turnover per day. (Day, total turnover(excluding VAT) per day) */
SELECT date_format(g.date,'%Y-%m-%d') as 'day',
       round(sum(g.turnover) / 1.21, 2) as total_turnover_vat_excluded
from brands b
         inner join gmv g
                    on b.id = g.brand_id
where g.date between date('2018-04-24') and date('2018-04-30')
group by g.date