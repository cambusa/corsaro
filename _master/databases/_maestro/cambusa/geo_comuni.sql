CREATE VIEW GEO_COMUNI AS 
SELECT 
    GEOCOMUNI.SYSID AS SYSID,
    GEOCOMUNI.DESCRIPTION AS DESCRIPTION, 
    GEOCOMUNI.CAP AS CAP, 
    GEOCOMUNI.PROVINCIAID AS PROVINCIAID,
    GEOPROVINCE.SIGLA AS SIGLA 
FROM GEOCOMUNI 
INNER JOIN GEOPROVINCE ON GEOPROVINCE.SYSID=GEOCOMUNI.PROVINCIAID