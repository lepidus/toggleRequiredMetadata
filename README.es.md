# Módulo Toggle Required Metadata

[English](README.md) · [Português (Brasil)](README.pt_BR.md) · **Español**

Este módulo permite que una revista, editorial o servidor exija a las personas colaboradoras de un envío más de lo que la aplicación exige por sí sola: la **Afiliación**, la **Reseña biográfica** y el **ORCID iD** pueden hacerse obligatorios.

Cada requisito se marca en el formulario de Colaborador, donde la persona autora lo completa, y se comprueba de nuevo en el tercer paso del envío, de modo que ningún envío se recibe sin él.

El requisito de ORCID depende del [módulo ORCID Profile](https://github.com/pkp/orcidProfile). Mientras está habilitado, el iD no se escribe sino que lo autoriza la persona colaboradora, así que lo que este módulo exige es que **al menos una persona colaboradora tenga un ORCID iD autenticado** y que la **autorización se haya solicitado a todas las demás**. Mientras está deshabilitado, el iD se escribe en el formulario de Colaborador, y toda persona colaboradora debe tener uno.

## Compatibilidad

La versión más reciente de este módulo es compatible con las siguientes aplicaciones de PKP:

* OJS 3.3.0
* OPS 3.3.0
* OMP 3.3.0

Las líneas más nuevas de la aplicación se atienden en otras ramas de este módulo: `stable-3_4_0` (OJS, OPS y OMP 3.4) y `main` (OJS, OPS y OMP 3.5).

## Instalación

Instale el módulo **Toggle Required Metadata** desde la **Galería de módulos**, en **Panel > Sitio web > Módulos**

## Ajustes

Habilitar el módulo no exige nada por sí solo: cada requisito queda desactivado hasta que se elija. En los ajustes del módulo se elige cuáles debe cumplir un envío:

![La sección de ajustes del módulo](/assets/plugin-settings-section.png)

![Los ajustes del módulo con los requisitos habilitados](/assets/plugin-settings.png)

## Dónde se aplica cada requisito

| Opción | Formulario de Colaborador | Tercer paso del envío |
|---|---|---|
| Afiliación | marcada como obligatoria | bloquea hasta que toda persona colaboradora tenga una |
| Reseña biográfica | marcada como obligatoria, y el formulario no puede guardarse sin ella | bloquea hasta que toda persona colaboradora tenga una |
| ORCID | marcado como obligatorio solo mientras el módulo ORCID Profile está deshabilitado, ya que un iD autorizado no se puede escribir | bloquea hasta que toda persona colaboradora tenga un iD o, con el módulo ORCID Profile habilitado, hasta que una lo haya autenticado y la autorización se haya solicitado a las demás |

### En el formulario de Colaborador

El metadato seleccionado se marca como obligatorio donde la persona autora lo completa:

![El formulario de Colaborador con los metadatos obligatorios](/assets/application.png)

### En el tercer paso del envío

Cada requisito se comprueba de nuevo antes de que el envío avance, y lo que falta se lista justo debajo de las personas colaboradoras, para que quien envía sepa qué le queda por completar.

### Lo que la comprobación no cubre

Los requisitos son sobre lo que llega por el formulario de envío, y ahí es donde se aplican: se comprueban en el tercer paso del envío, y en ningún otro lugar. Un colaborador editado después en el flujo de trabajo, un envío creado por la API REST y todo lo que entra por importación o por QuickSubmit no pasan por la comprobación, y la publicación tampoco. Lo que el módulo guarda es la puerta: ningún envío se *recibe* sin los metadatos, lo que no es lo mismo que ningún envío de la revista se quede sin ellos.

## Con el envío ya en el flujo de trabajo

Una vez que el envío entra, el requisito de ORCID todavía puede estar esperando a una persona colaboradora que no autorizó su iD. El flujo de trabajo dice quién, con la indicación que corresponde a quien lee — una persona editora puede volver a solicitar la autorización, una autora solo puede insistir con la colaboradora.

# Licencia
__Este módulo está licenciado bajo la GNU General Public License v3.0__

__Copyright (c) 2022-2026 Lepidus Tecnologia__
