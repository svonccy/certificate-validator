import os
from pathlib import Path

def cargar_trust_roots() -> list:
    """Carga y retorna los certificados raíz de confianza configurados en el entorno."""
    rutas = os.environ.get("CNSM_TRUST_ROOTS", "").strip()
    if not rutas:
        return []

    trust_roots = []
    for ruta in rutas.split(","):
        ruta = ruta.strip()
        if not ruta:
            continue
        ruta_path = Path(ruta)
        if not ruta_path.exists():
            continue
        from pyhanko.keys import load_cert_from_pemder
        trust_roots.append(load_cert_from_pemder(str(ruta_path)))

    return trust_roots
