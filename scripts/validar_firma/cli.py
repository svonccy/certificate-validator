import json
from typing import Dict, Any, Optional

def enviar_respuesta(data: Dict[str, Any]) -> int:
    """Imprime el resultado estructurado en formato JSON y retorna 0."""
    print(json.dumps(data, ensure_ascii=True))
    return 0

def error_respuesta(motivo: str, hash_pdf: Optional[str] = None) -> Dict[str, Any]:
    """Genera la estructura estándar del JSON en caso de error."""
    return {
        "valido": False,
        "motivo": motivo,
        "firma": {"hash_pdf": hash_pdf} if hash_pdf else {},
        "firmante": {},
        "certificado": {},
        "detalle": "",
    }
    
