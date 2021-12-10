select routine_schema as database_name,
       routine_name,
       routine_type as type,
       data_type as return_type
from information_schema.routines
where routine_schema not in ('sys', 'information_schema',
                             'mysql', 'performance_schema')
order by routine_schema,
         routine_name;


drop table articles;
drop table regeventos;
drop table informacion;
--Eliminar del codigo las referencias a este recurso.
drop table recurso_mmd; 
drop table rmmd_temp;
--
drop table ioe_refs;