CREATE VIEW QW_COLLOCAZIONI AS 
SELECT 
    QVOBJECTS.SYSID AS SYSID,
    QVOBJECTS.DESCRIPTION AS DESCRIPTION,
    QVOBJECTS.REGISTRY AS REGISTRY,
    QVOBJECTS.TYPOLOGYID AS TYPOLOGYID,
    QVOBJECTS.TAG AS TAG,
    QVOBJECTS.REFGENREID AS REFGENREID,
    OBJECTS_COLLOCAZIONI.MAGAZZINOID AS MAGAZZINOID,
    OBJECTS_COLLOCAZIONI.ZONA AS ZONA,
    OBJECTS_COLLOCAZIONI.SCAFFALE AS SCAFFALE,
    OBJECTS_COLLOCAZIONI.RIPIANO AS RIPIANO,
    OBJECTS_COLLOCAZIONI.COORDINATA AS COORDINATA
FROM QVOBJECTS
INNER JOIN OBJECTS_COLLOCAZIONI ON OBJECTS_COLLOCAZIONI.SYSID=QVOBJECTS.SYSID 
WHERE 
    QVOBJECTS.TYPOLOGYID=[:SYSID(0COLLOCAZ000)] AND QVOBJECTS.DELETED=0
