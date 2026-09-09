# Módulo Toggle Required Metadata

[English](README.md) · [Português (Brasil)](README.pt_BR.md) · **Español**

Este módulo permite que una revista, editorial o servidor exija a los colaboradores de un envío más de lo que la aplicación exige por sí sola: la **Afiliación**, el **Resumen Biográfico** y el **ORCID iD** pueden hacerse obligatorios, cada uno por separado.

Cada requisito se comprueba al finalizar el envío, de modo que ningún envío pasa sin él. Los tres se marcan además como obligatorios en el formulario de Colaborador, donde el autor los completa.

El requisito de ORCID es de otra naturaleza, porque desde la 3.5 nadie escribe un ORCID iD: solo queda registrado cuando el colaborador lo autoriza mediante el correo de verificación. Por eso lo que el módulo exige es que **al menos un colaborador tenga el ORCID iD verificado** y que la **verificación se haya solicitado a todos los demás**. En el último paso del envío esto se muestra como un único bloque, que cuenta cuántos colaboradores ya verificaron e indica en qué punto está cada uno, para que el autor distinga lo que aún depende de él de lo que solo espera a otra persona.

![El requisito de ORCID en el último paso de un envío](/assets/review-orcid-requirement.png)

## Compatibilidad

Este módulo es compatible con las siguientes aplicaciones de PKP:

* OJS 3.5.0
* OPS 3.5.0
* OMP 3.5.0

Las líneas de versión anteriores se atienden en otras ramas de este módulo: `stable-3_3_0` (OJS, OPS y OMP 3.3) y `stable-3_4_0` (OJS, OPS y OMP 3.4).

## Instalación

Instale el módulo **Toggle Required Metadata** desde la **Galería de Módulos**, en **Panel de control > Sitio web > Módulos**

## Ajustes

Los tres requisitos vienen habilitados de forma predeterminada. Los ajustes del módulo, accesibles desde **Panel de control > Sitio web > Módulos**, son donde se elige cuáles debe cumplir un envío:

![Los ajustes del módulo con los tres requisitos habilitados](/assets/settings-orcid-enabled.png)

La opción ORCID solo está disponible mientras ORCID esté habilitado en los ajustes de ORCID de la aplicación. Cuando no lo está, la opción aparece deshabilitada, junto con una nota que explica el motivo, y la elección guardada se mantiene intacta:

![Los ajustes del módulo con ORCID no habilitado en la aplicación](/assets/settings-orcid-disabled.png)

## Dónde se aplica cada requisito

| Opción | Formulario de Colaborador | Finalización del envío |
|---|---|---|
| Afiliación | marcada como obligatoria | bloqueada hasta que todo colaborador tenga al menos una afiliación |
| Resumen Biográfico | marcado como obligatorio; el formulario no puede guardarse sin él | bloqueada hasta que todo colaborador tenga uno |
| ORCID | marcado como obligatorio junto al botón de verificación; no editable a mano en la 3.5 | bloqueada hasta que un colaborador tenga el iD verificado y la verificación se haya solicitado a todos los demás, mostrado como un único bloque con la situación de cada colaborador |

### En el formulario de Colaborador

Los metadatos seleccionados se marcan como obligatorios allí donde el autor los completa. El ORCID iD se marca junto al botón de verificación, que en la 3.5 es la única vía por la que un colaborador puede aportar uno:

![El campo ORCID del formulario de Colaborador, marcado como obligatorio](/assets/contributor-form-orcid-required.png)

![El mismo campo una vez solicitada la verificación](/assets/contributor-form-orcid-requested.png)

### Al finalizar el envío

Cada requisito se comprueba de nuevo en el último paso, y el envío no puede enviarse hasta que todos se cumplan. Los avisos quedan agrupados en la parte superior del panel de colaboradores, junto al botón Editar que los resuelve:

![El último paso de un envío con los tres requisitos sin cumplir](/assets/review-all-requirements.png)

### Lo que la comprobación no cubre

Los requisitos son sobre lo que llega por el formulario de envío, y ahí es donde se aplican: se comprueban cuando la persona autora finaliza el envío, y en ningún otro lugar. Un colaborador editado después en el flujo de trabajo, un envío creado por la API REST y todo lo que entra por importación o por QuickSubmit no pasan por la comprobación, y la publicación tampoco. Lo que el módulo guarda es la puerta: ningún envío se *recibe* sin los metadatos, lo que no es lo mismo que ningún envío de la revista se quede sin ellos.

## Con el envío ya en el flujo de trabajo

Una vez enviado, el requisito de ORCID todavía puede estar esperando a un colaborador que no abrió el correo de verificación. El flujo de trabajo dice quién es, con el consejo que corresponde a quien lee — un editor puede reenviar el correo, un autor solo puede insistirle al colaborador:

![El aviso de ORCID como lo ve un editor](/assets/workflow-warning-editor.png)

![El aviso de ORCID como lo ve un autor](/assets/workflow-warning-author.png)

## Notas sobre la 3.5

* **No se puede exigir que el colaborador escriba un ORCID iD.** Desde la 3.5 la aplicación rechaza cualquier ORCID iD enviado desde el formulario de Colaborador; un iD solo queda registrado mediante el flujo de verificación de ORCID. Versiones anteriores de este módulo exigían el iD escrito siempre que el módulo ORCID Profile estuviera desactivado, y eso ya no es posible.
* **La opción ORCID sigue los ajustes de ORCID de la aplicación.** Con ORCID deshabilitado la opción no surte efecto, y la elección guardada se mantiene y vuelve a aplicarse en cuanto se habilite ORCID. Tenga en cuenta que ORCID también puede habilitarse para toda la instalación, a nivel de sitio, lo que activa el requisito para todas las revistas, editoriales o servidores a la vez.
* **La afiliación y el ORCID iD se marcan en el formulario de Colaborador, pero se comprueban al finalizar el envío.** Ambos campos renderizan su propia etiqueta, así que el módulo añade un elemento junto a cada uno para llevar la marca de obligatoriedad. Ninguno de los dos se señala como obligatorio ante el propio formulario: ninguno limpia un error una vez levantado, lo que dejaría el formulario de Colaborador imposible de guardar.

## Compilación de los recursos de front-end

Las marcas de obligatoriedad en los campos de afiliación y ORCID, el bloque de ORCID en el último paso del envío y el aviso de ORCID en el flujo de trabajo son componentes Vue, compilados con Vite. El resultado de la compilación está versionado en `public/build/`, de modo que instalar el módulo no requiere ningún paso de compilación. Para recompilar tras cambiar algo en `resources/`:

```
npm install
npm run build
```

# Licencia
__Este módulo está licenciado bajo la GNU General Public License v3.0__

__Copyright (c) 2022-2026 Lepidus Tecnologia__
