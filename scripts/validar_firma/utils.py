import hashlib
from datetime import datetime
from pathlib import Path

def sha256_archivo(ruta: Path) -> str:
    """Calcula el hash SHA-256 de un archivo en bloques."""
    hash_obj = hashlib.sha256()
    with ruta.open("rb") as archivo:
        for bloque in iter(lambda: archivo.read(8192), b""):
            hash_obj.update(bloque)
    return f"sha256:{hash_obj.hexdigest()}"

def iso(dt) -> str | None:
    """Formatea un objeto datetime a formato ISO 8601 o devuelve su representación en string."""
    if dt is None:
        return None
    if isinstance(dt, datetime):
        return dt.isoformat()
    return str(dt)
