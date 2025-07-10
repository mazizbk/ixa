declare global {
    interface Window {
        montgolfiereKnob: any;
        changeSection: (sections: number, container: HTMLElement, event?: Event) => undefined | false;
    }
}

import './style.scss';
import './sections.ts';
import './questions.ts';
import './scrollable.ts';
import 'product-tour-js';
import 'product-tour-js/lib.css';

document.querySelectorAll<HTMLElement>('[data-action="toggle"]').forEach((el: HTMLElement) => {
    const target = document.querySelectorAll<HTMLElement>(el.dataset.target!);
    el.addEventListener('click', (e: Event) => {
        e.preventDefault();
        target.forEach((target: HTMLElement) => {
            target.classList.toggle('active');
        });
    });
});

// Transform keyup events (for Enter key) to click events for elements that can be focused
document.querySelectorAll<HTMLElement>('[data-focus-enter-click]').forEach((el: HTMLElement) => {
    el.addEventListener('keyup', (e: KeyboardEvent) => {
        if(['Enter', 'NumpadEnter'].includes(e.code)) {
            el.dispatchEvent(new MouseEvent('click'));
        }
    });
});

if (module.hot) {
    module.hot.accept();
}
