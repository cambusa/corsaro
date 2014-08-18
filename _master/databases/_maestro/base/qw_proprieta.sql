CREATE VIEW QW_PROPRIETA AS 
SELECT 
    QVOBJECTS.SYSID,
    QVOBJECTS.NAME,
    QVOBJECTS.DESCRIPTION,
    QVOBJECTS.REGISTRY,
    QVOBJECTS.TYPOLOGYID,
    QVOBJECTS.REFGENREID,
    QVOBJECTS.REFOBJECTID,
    QVOBJECTS.REFQUIVERID,
    QVOBJECTS.BEGINTIME,
    QVOBJECTS.ENDTIME,
    QVOBJECTS.REFERENCE,
    QVOBJECTS.AUXTIME,
    QVOBJECTS.AUXAMOUNT,
    QVOBJECTS.TAG,
    QVOBJECTS.CONSISTENCY,
    QVOBJECTS.SCOPE,
    QVOBJECTS.UPDATING,
    QVOBJECTS.DELETING,
    QVOBJECTS.ROLEID,
    QVOBJECTS.USERINSERTID,
    QVOBJECTS.USERUPDATEID,
    QVOBJECTS.USERDELETEID,
    QVOBJECTS.TIMEINSERT,
    QVOBJECTS.TIMEUPDATE,
    QVOBJECTS.TIMEDELETE,
    OBJECTS_PROPRIETA.SYSID AS EXTENSIONID,
    OBJECTS_PROPRIETA.INDIRIZZO AS INDIRIZZO,
    OBJECTS_PROPRIETA.CIVICO AS CIVICO,
    OBJECTS_PROPRIETA.CAP AS CAP,
    OBJECTS_PROPRIETA.CITTA AS CITTA,
    OBJECTS_PROPRIETA.PROVINCIA AS PROVINCIA,
    OBJECTS_PROPRIETA.NAZIONE AS NAZIONE,
    OBJECTS_PROPRIETA.CODFISC AS CODFISC,
    OBJECTS_PROPRIETA.PIVA AS PIVA,
    OBJECTS_PROPRIETA.CCIAA AS CCIAA,
    OBJECTS_PROPRIETA.ATECO AS ATECO,
    OBJECTS_PROPRIETA.ABI AS ABI,
    OBJECTS_PROPRIETA.CAB AS CAB,
    OBJECTS_PROPRIETA.TELEFONO AS TELEFONO,
    OBJECTS_PROPRIETA.CELLULARE AS CELLULARE,
    OBJECTS_PROPRIETA.FAX AS FAX,
    OBJECTS_PROPRIETA.EMAIL AS EMAIL,
    OBJECTS_PROPRIETA.CONTODEFAULTID AS CONTODEFAULTID,
    OBJECTS_PROPRIETA.TITOLAREID AS TITOLAREID 
FROM QVOBJECTS 
LEFT JOIN OBJECTS_PROPRIETA ON OBJECTS_PROPRIETA.SYSID=QVOBJECTS.SYSID 
WHERE 
    QVOBJECTS.TYPOLOGYID=[:SYSID(0PROPRIETA0000)] AND QVOBJECTS.DELETED=0