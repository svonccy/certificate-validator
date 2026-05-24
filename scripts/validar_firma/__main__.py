import sys
from pathlib import Path

# Ajustar sys.path para permitir la importación del paquete validar_firma
package_dir = Path(__file__).resolve().parent.parent
if str(package_dir) not in sys.path:
    sys.path.insert(0, str(package_dir))

from validar_firma.cli import enviar_respuesta, error_respuesta
from validar_firma.utils import sha256_archivo
from validar_firma.trust_roots import cargar_trust_roots
from validar_firma.validators import PyHankoPdfValidator

def main() -> int:
    if len(sys.argv) < 2:
        return enviar_respuesta(error_respuesta("Ruta del PDF no proporcionada."))

    token_borrador = None
    if "--token" in sys.argv:
        try:
            indice = sys.argv.index("--token")
            token_borrador = sys.argv[indice + 1]
        except Exception:
            token_borrador = None

    ruta_pdf = Path(sys.argv[1])
    if not ruta_pdf.exists():
        return enviar_respuesta(error_respuesta("El PDF firmado no existe."))

    try:
        trust_roots = cargar_trust_roots()
        validator = PyHankoPdfValidator(trust_roots)
        resultado = validator.validar(ruta_pdf, token_borrador)
        return enviar_respuesta(resultado)
    except ImportError as exc:
        return enviar_respuesta(error_respuesta(str(exc)))
    except Exception as exc:
        hash_pdf = None
        try:
            hash_pdf = sha256_archivo(ruta_pdf)
        except Exception:
            pass
        return enviar_respuesta(error_respuesta(f"Error de validacion: {exc}", hash_pdf))

if __name__ == "__main__":
    sys.exit(main())
