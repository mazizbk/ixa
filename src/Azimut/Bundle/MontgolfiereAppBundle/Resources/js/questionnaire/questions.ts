window.montgolfiereKnob = require('../questionnaire.js');

document.querySelectorAll<HTMLInputElement>('input[type=radio],input[type=checkbox]').forEach((el: HTMLInputElement) => {
    el.addEventListener('click', () => {
        if(el.type==='radio') {
            el.form!.querySelectorAll<HTMLInputElement>('input[name="'+el.name+'"]').forEach((el2: HTMLInputElement) => el2.parentElement!.classList.remove('checked'));
        }
        el.parentElement!.classList.toggle('checked');
    });
    el.parentElement!.addEventListener('keypress', function(e) {
        if(['Enter', 'NumpadEnter'].includes(e.code)) {
            this.querySelector('input[type=radio],input[type=checkbox]')!.dispatchEvent(new MouseEvent('click'));
        }
    });
});
