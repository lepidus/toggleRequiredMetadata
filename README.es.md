# Módulo Toggle Required Metadata

[English](README.md) · [Português (Brasil)](README.pt_BR.md) · **Español**

Este módulo permite que una revista, editorial o servidor exija a los colaboradores de un envío más de lo que la aplicación exige por sí sola: la **Afiliación**, el **Resumen Biográfico** y el **ORCID iD** pueden hacerse obligatorios, cada uno por separado.

Cada requisito se comprueba al finalizar el envío, de modo que ningún envío pasa sin él. Los tres se marcan además como obligatorios en el formulario de Colaborador, donde el autor los completa.

El requisito de ORCID es de otra naturaleza, porque desde la 3.5 nadie escribe un ORCID iD: solo queda registrado cuando el colaborador lo autoriza mediante el correo de verificación. Por eso lo que el módulo exige es que **al menos un colaborador tenga el ORCID iD verificado** y que la **verificación se haya solicitado a todos los demás**. En el último paso del envío esto se muestra como un único bloque, que cuenta cuántos colaboradores ya verificaron e indica en qué punto está cada uno, para que el autor distinga lo que aún depende de él de lo que solo espera a otra persona. Mientras algún colaborador siga sin verificación, el flujo de trabajo del envío muestra un aviso que lo nombra.

## Compatibilidad

Este módulo es compatible con las siguientes aplicaciones de PKP:

* OJS 3.5.0
* OPS 3.5.0
* OMP 3.5.0

Las líneas de versión anteriores se atienden en otras ramas de este módulo: `stable-3_3_0` (OJS 3.3) y `stable-3_4_0` (OJS, OPS y OMP 3.4).

## Instalación

Instale el módulo **Toggle Required Metadata** desde la **Galería de Módulos**, en **Panel de control > Sitio web > Módulos**

## Ajustes

Los tres requisitos vienen habilitados de forma predeterminada. Para cambiarlos:

1. Una vez instalado el módulo, vaya a sus ajustes:
![plugin-settings-section](/assets/plugin-settings-section.png)

2. Seleccione los metadatos que se exigirán. La opción ORCID solo está disponible mientras ORCID esté habilitado en los ajustes de ORCID de la aplicación; cuando no lo está, la opción aparece deshabilitada, junto con una nota que explica el motivo:
![plugin-settings](/assets/plugin-settings.png)

3. El formulario de Colaborador pasa entonces a marcar como obligatorios los metadatos seleccionados:
![plugin-in-action](/assets/application.png)

## Dónde se aplica cada requisito

| Opción | Formulario de Colaborador | Finalización del envío |
|---|---|---|
| Afiliación | marcada como obligatoria | bloqueada hasta que todo colaborador tenga al menos una afiliación |
| Resumen Biográfico | marcado como obligatorio; el formulario no puede guardarse sin él | bloqueada hasta que todo colaborador tenga uno |
| ORCID | marcado como obligatorio junto al botón de verificación; no editable a mano en la 3.5 | bloqueada hasta que un colaborador tenga el iD verificado y la verificación se haya solicitado a todos los demás, mostrado como un único bloque con la situación de cada colaborador |

## Notas sobre la 3.5

* **No se puede exigir que el colaborador escriba un ORCID iD.** Desde la 3.5 la aplicación rechaza cualquier ORCID iD enviado desde el formulario de Colaborador; un iD solo queda registrado mediante el flujo de verificación de ORCID. Versiones anteriores de este módulo exigían el iD escrito siempre que el módulo ORCID Profile estuviera desactivado, y eso ya no es posible.
* **La opción ORCID sigue los ajustes de ORCID de la aplicación.** Con ORCID deshabilitado la opción no surte efecto, y la elección guardada se mantiene y vuelve a aplicarse en cuanto se habilite ORCID. Tenga en cuenta que ORCID también puede habilitarse para toda la instalación, a nivel de sitio, lo que activa el requisito para todas las revistas, editoriales o servidores a la vez.
* **La afiliación y el ORCID iD se marcan en el formulario de Colaborador, pero se comprueban al finalizar el envío.** Ambos campos de la 3.5 renderizan su propia etiqueta e ignoran la que se configura para ellos, así que el módulo añade un elemento junto a cada uno para llevar la marca de obligatoriedad. Ninguno de los dos campos se señala como obligatorio ante el propio formulario, porque en ambos casos el formulario ya no podría guardarse nunca: añadir una afiliación cambia el valor del campo sin emitir un evento de cambio, y solicitar la verificación de ORCID de un colaborador que ya existe envía el correo por un endpoint aparte, sin tocar el formulario. En ambos casos, un error levantado para el campo jamás se limpiaría.

## Compilación de los recursos de front-end

Las marcas de obligatoriedad en los campos de afiliación y ORCID, el bloque de ORCID en el último paso del envío y el aviso de ORCID en el flujo de trabajo son componentes Vue, compilados con Vite. El resultado de la compilación está versionado en `public/build/`, de modo que instalar el módulo no requiere ningún paso de compilación. Para recompilar tras cambiar algo en `resources/`:

```
npm install
npm run build
```

# Licencia
__Este módulo está licenciado bajo la GNU General Public License v3.0__

__Copyright (c) 2022-2026 Lepidus Tecnologia__
