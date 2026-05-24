from abc import ABC, abstractmethod
from pathlib import Path
from typing import Dict, Any, Optional
from validar_firma.utils import sha256_archivo, iso

class SignatureValidator(ABC):
    @abstractmethod
    def validar(self, ruta_pdf: Path, token_borrador: Optional[str] = None) -> Dict[str, Any]:
        """Valida la firma de un archivo y devuelve un diccionario con los resultados."""
        pass

class PyHankoPdfValidator(SignatureValidator):
    def __init__(self, trust_roots: list):
        self.trust_roots = trust_roots

    def _extraer_cn(self, subject) -> Optional[str]:
        try:
            data = subject.native
            cn = data.get("common_name")
            if isinstance(cn, list):
                return cn[0] if cn else None
            return cn
        except Exception:
            return None

    def _extraer_documento(self, subject) -> Optional[str]:
        try:
            data = subject.native
            doc = data.get("serial_number")
            if isinstance(doc, list):
                return doc[0] if doc else None
            return doc
        except Exception:
            return None

    def validar(self, ruta_pdf: Path, token_borrador: Optional[str] = None) -> Dict[str, Any]:
        # Carga dinámica de dependencias criptográficas
        try:
            from pyhanko.pdf_utils.reader import PdfFileReader
            from pyhanko.sign.validation import validate_pdf_signature
            from pyhanko_certvalidator import ValidationContext
        except Exception as exc:
            raise ImportError(f"Dependencias de validacion no disponibles: {exc}")

        hash_pdf = sha256_archivo(ruta_pdf)
        vc = ValidationContext(trust_roots=self.trust_roots)

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
                return {
                    "valido": False,
                    "motivo": "No se encontraron firmas en el PDF.",
                    "firma": {"hash_pdf": hash_pdf},
                    "firmante": {},
                    "certificado": {},
                    "detalle": "",
                }

            firma_pdf = firmas[-1]
            estado = validate_pdf_signature(firma_pdf, vc)

        intacto = bool(getattr(estado, "intact", False))
        valido_cripto = bool(getattr(estado, "valid", intacto))
        confiable = bool(getattr(estado, "trusted", False)) if self.trust_roots else False

        firmante_cert = getattr(estado, "signer_cert", None)
        subject = getattr(firmante_cert, "subject", None) if firmante_cert else None
        issuer = getattr(firmante_cert, "issuer", None) if firmante_cert else None

        resultado = {
            "valido": valido_cripto,
            "motivo": None,
            "firma": {
                "fecha_firma": iso(getattr(estado, "signing_time", None)),
                "algoritmo": str(getattr(estado, "md_algorithm", "")) or None,
                "serial": hex(getattr(firmante_cert, "serial_number", 0)) if firmante_cert else None,
                "hash_pdf": hash_pdf,
                "cadena_confiable": confiable,
                "integridad": intacto,
            },
            "borrador_coincide": token_encontrado,
            "firmante": {
                "nombre": self._extraer_cn(subject) if subject else None,
                "documento": self._extraer_documento(subject) if subject else None,
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
        elif self.trust_roots and not confiable:
            resultado["motivo"] = "No se pudo verificar la cadena de confianza."

        return resultado
