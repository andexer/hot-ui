// overlays
import dialog from './ui/dialog.js';
import alertDialog from './ui/alert-dialog.js';
import sheet from './ui/sheet.js';
import drawer from './ui/drawer.js';
import popover from './ui/popover.js';
import command from './ui/command.js';
// forms
import calendar from './ui/calendar.js';
import datePicker from './ui/date-picker.js';
import datetimePicker from './ui/datetime-picker.js';
import timeField from './ui/time-field.js';
import colorPicker from './ui/color-picker.js';
import inputMask from './ui/input-mask.js';
import tagsInput from './ui/tags-input.js';
import signaturePad from './ui/signature-pad.js';
import richTextEditor from './ui/rich-text-editor.js';
import markdownEditor from './ui/markdown-editor.js';
import mentionInput from './ui/mention-input.js';
import fileUpload from './ui/file-upload.js';
// data
import dataTable from './ui/data-table.js';
import tree from './ui/tree.js';
import kanban from './ui/kanban.js';
// nav / misc
import accordion from './ui/accordion.js';
import carousel from './ui/carousel.js';
import sidebar from './ui/sidebar.js';
import scrollspy from './ui/scrollspy.js';
import onboardingTour from './ui/onboarding-tour.js';
import infiniteScroll from './ui/infinite-scroll.js';
import countdown from './ui/countdown.js';
import typewriter from './ui/typewriter.js';
import streamingText from './ui/streaming-text.js';
import numberTicker from './ui/number-ticker.js';
import textReveal from './ui/text-reveal.js';
/**
 * Registro central de islas — el ÚNICO archivo que tocas para añadir una.
 *
 * Cada isla vive en components/ui/<componente>.ts (espejo exacto del .php),
 * exporta un IslandPlugin por defecto y el cargador (app.ts) la monta dentro
 * de 'alpine:init', antes de Alpine.start().
 *
 * Familias SIN isla a propósito: los motores del kernel (hotMenu/hotMenubar/
 * hotSelect/hotListbox/hotCommand), las directivas x-hot-* y la magic $hot
 * cubren dropdown/context-menu/menubar/select/combobox, tooltip, hover-card,
 * tabs, stepper, navigation-menu, input-otp, server-table, scheduler y gantt.
 */
export const ISLANDS = [
    // overlays
    dialog,
    alertDialog,
    sheet,
    drawer,
    popover,
    command,
    // forms
    calendar,
    datePicker,
    datetimePicker,
    timeField,
    colorPicker,
    inputMask,
    tagsInput,
    signaturePad,
    richTextEditor,
    markdownEditor,
    mentionInput,
    fileUpload,
    // data
    dataTable,
    tree,
    kanban,
    // nav / misc
    accordion,
    carousel,
    sidebar,
    scrollspy,
    onboardingTour,
    infiniteScroll,
    countdown,
    typewriter,
    streamingText,
    numberTicker,
    textReveal,
];
//# sourceMappingURL=islands.js.map