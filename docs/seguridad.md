# Seguridad en Hot-UI

Modelo mental: **el marcado es de confianza; los datos, nunca**.

## Escapado (capa PHP)

| Contexto | Herramienta | Regla |
|---|---|---|
| Texto HTML | `e($valor)` | obligatorio para todo eco dinámico (`ENT_QUOTES`, UTF-8) |
| Atributos HTML | `AttributeBag::__toString` | escapa todo valor; `true`→atributo desnudo, `false/null`→omitido |
| Nombres de atributo | patrón estricto | un nombre inválido **lanza excepción**, jamás se silencia |
| URLs (`href/src/cite`) | `safe_url()` | allowlist `http/https/mailto/tel/sms/ftp/#/relativo`; esquemas peligrosos → `#` |
| JS dentro de atributos | `js($valor)` | JSON con flags HEX + comillas estructurales como `\u0022`: imposible romper `x-data="..."` |
| Slots | crudos por contrato | ya son HTML renderizado por otros componentes de confianza |

## Superficie JS

- Sin `eval` ni `new Function` en kernel ni islas.
- El renderer markdown de la isla editor escapa primero y renderiza después
  (subset seguro, sin HTML crudo del autor).
- `data-hot-model="ruta"` resuelve contra el scope x-data local: no cruza
  orígenes ni evalúa nada; solo lee/escribe propiedades reactivas.
- Los ids generados (`mintId`) son internos y siempre re-escritos en el mismo
  pase que las referencias (`aria-labelledby/describedby`).

## Re-derivación ante mutaciones del DOM

Las directivas `x-hot-*` no "aplican y olvidan": `keepWired()` re-dedupe la
wiring ARIA cada vez que el subtree cambia, retirando SOLO lo que ella misma
aportó. Un atributo escrito a mano por el autor sobrevive siempre.

## Checklist antes de tocar una vista

1. ¿Todo texto dinámico pasa por `e()`?
2. ¿Toda URL de usuario pasa por `safe_url()`?
3. ¿Algún dato llega a un atributo Alpine? → `js()`
4. ¿El componente acepta atributos libres? → van al bag y salen escapados
