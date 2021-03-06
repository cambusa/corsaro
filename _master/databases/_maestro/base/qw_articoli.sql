CREATE VIEW QW_ARTICOLI AS 
SELECT 
    QVGENRES.SYSID AS SYSID,
    QVGENRES.DESCRIPTION AS DESCRIPTION,
    QVGENRES.REGISTRY AS REGISTRY,
    QVGENRES.TYPOLOGYID AS TYPOLOGYID,
    QVGENRES.ROUNDING AS ROUNDING,
    QVGENRES.TAG AS TAG,
    GENRES_ARTICOLI.CODICE AS CODICE,
    GENRES_ARTICOLI.PRODUTTOREID AS PRODUTTOREID,
    GENRES_ARTICOLI.PROCESSOID AS PROCESSOID,
    GENRES_ARTICOLI.PRODOTTO AS PRODOTTO,
    GENRES_ARTICOLI.VARIANTE AS VARIANTE
FROM QVGENRES 
INNER JOIN GENRES_ARTICOLI ON GENRES_ARTICOLI.SYSID=QVGENRES.SYSID 
WHERE 
    QVGENRES.TYPOLOGYID=[:SYSID(0ARTICOLI00000)] AND QVGENRES.DELETED=0
