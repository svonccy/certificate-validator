#!/usr/bin/env python3
import hashlib
import json
import os
import sys
from datetime import datetime
from pathlib import Path


def _sha256_archivo(ruta: Path) -> str:
    hash_obj = hashlib.sha256()
    with ruta.open("rb") as archivo:
        for bloque in iter(lambda: archivo.read(8192), b""):
            hash_obj.update(bloque)
    return f"sha256:{hash_obj.hexdigest()}"


def _iso(dt) -> str | None:
    if dt is None:
        return None
    if isinstance(dt, datetime):
        return dt.isoformat()
    return str(dt)


def _extraer_cn(subject) -> str | None:
    try:
        data = subject.native
        cn = data.get("common_name")
        if isinstance(cn, list):
            return cn[0] if cn else None
        return cn
    except Exception:
        return None


def _extraer_documento(subject) -> str | None:
    try:
        data = subject.native
        doc = data.get("serial_number")
        if isinstance(doc, list):
            return doc[0] if doc else None
        return doc
    except Exception:
        return None


def _cargar_trust_roots() -> list:
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


def _respuesta(data: dict) -> int:
    print(json.dumps(data, ensure_ascii=True))
    return 0


def main() -> int:
    if len(sys.argv) < 2:
        return _respuesta({
            "valido": False,
            "motivo": "Ruta del PDF no proporcionada.",
            "firma": {},
            "firmante": {},
            "certificado": {},
            "detalle": "",
        })

    token_borrador = None
    if "--token" in sys.argv:
        try:
            indice = sys.argv.index("--token")
            token_borrador = sys.argv[indice + 1]
        except Exception:
            token_borrador = None

    ruta_pdf = Path(sys.argv[1])
    if not ruta_pdf.exists():
        return _respuesta({
            "valido": False,
            "motivo": "El PDF firmado no existe.",
            "firma": {},
            "firmante": {},
            "certificado": {},
            "detalle": "",
        })

    try:
        from pyhanko.pdf_utils.reader import PdfFileReader
        from pyhanko.sign.validation import validate_pdf_signature
        from pyhanko_certvalidator import ValidationContext
    except Exception as exc:
        return _respuesta({
            "valido": False,
            "motivo": f"Dependencias de validacion no disponibles: {exc}",
            "firma": {},
            "firmante": {},
            "certificado": {},
            "detalle": "",
        })

    try:
        hash_pdf = _sha256_archivo(ruta_pdf)
        trust_roots = _cargar_trust_roots()
        vc = ValidationContext(trust_roots=trust_roots)

        token_encontrado = None
        if token_borrador:
            try:
                contenido = ruta_pdf.read_bytes()
                token_encontrado = token_borrador.encode("utf-8") in contenido
            except Exception:
                token_encontrado = False

        with ruta_pdf.open("rb") as documento:
            lector = PdfFileReader(documento)
            firmas = list(lector.embedded_signatures)

            if not firmas:
                return _respuesta({
                    "valido": False,
                    "motivo": "No se encontraron firmas en el PDF.",
                    "firma": {"hash_pdf": hash_pdf},
                    "firmante": {},
                    "certificado": {},
                    "detalle": "",
                })

            firma_pdf = firmas[-1]
            estado = validate_pdf_signature(firma_pdf, vc)

        intacto = bool(getattr(estado, "intact", False))
        valido_cripto = bool(getattr(estado, "valid", intacto))
        confiable = bool(getattr(estado, "trusted", False)) if trust_roots else False

        firmante_cert = getattr(estado, "signer_cert", None)
        subject = getattr(firmante_cert, "subject", None) if firmante_cert else None
        issuer = getattr(firmante_cert, "issuer", None) if firmante_cert else None

        resultado = {
            "valido": valido_cripto,
            "motivo": None,
            "firma": {
                "fecha_firma": _iso(getattr(estado, "signing_time", None)),
                "algoritmo": str(getattr(estado, "md_algorithm", "")) or None,
                "serial": hex(getattr(firmante_cert, "serial_number", 0)) if firmante_cert else None,
                "hash_pdf": hash_pdf,
                "cadena_confiable": confiable,
                "integridad": intacto,
            },
            "borrador_coincide": token_encontrado,
            "firmante": {
                "nombre": _extraer_cn(subject) if subject else None,
                "documento": _extraer_documento(subject) if subject else None,
            },
            "certificado": {
                "subject": subject.human_friendly if subject else None,
                "issuer": issuer.human_friendly if issuer else None,
            },
            "detalle": getattr(estado, "pretty_print_details", lambda: "")(),
        }

        if token_encontrado is False and resultado["motivo"] is None:
            resultado["motivo"] = "El PDF firmado no coincide con el borrador con QR."
        elif not valido_cripto:
            resultado["motivo"] = "La firma no es valida o el PDF fue alterado."
        elif trust_roots and not confiable:
            resultado["motivo"] = "No se pudo verificar la cadena de confianza."

        return _respuesta(resultado)
    except Exception as exc:
        return _respuesta({
            "valido": False,
            "motivo": f"Error de validacion: {exc}",
            "firma": {"hash_pdf": _sha256_archivo(ruta_pdf)},
            "firmante": {},
            "certificado": {},
            "detalle": "",
        })


if __name__ == "__main__":
    sys.exit(main())
