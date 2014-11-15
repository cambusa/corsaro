CREATE VIEW QW_CORSIJOIN AS
SELECT 
    QW_CORSI.SYSID AS SYSID, 
    QW_CORSI.DESCRIPTION AS DESCRIPTION,
    QW_CORSI.REGISTRY AS REGISTRY,
    QW_CORSI.REFERENCE AS REFERENCE,
    QW_CORSI.TAG AS TAG,
    QW_CORSI.TYPOLOGYID AS TYPOLOGYID,
    QW_CORSI.BEGINTIME AS BEGINTIME,
    QW_CORSI.ENDTIME AS ENDTIME,
    QW_CORSI.AUXAMOUNT AS AUXAMOUNT,
    QW_CORSI.AZIENDAID AS AZIENDAID,
    QW_CORSI.LUOGO AS LUOGO,
    QW_CORSI.TIPOCORSO AS TIPOCORSO,
    CASE 
    WHEN QW_CORSI.AZIENDAID<>'' AND QW_CORSI.AZIENDAID IS NOT NULL THEN QW_AZIENDE.DESCRIPTION
    ELSE QW_CORSI.REFERENTE 
    END AS REFERENTE,
    QW_AZIENDE.DESCRIPTION AS AZIENDA
FROM QW_CORSI
LEFT JOIN QW_AZIENDE ON QW_AZIENDE.SYSID=QW_CORSI.AZIENDAID
