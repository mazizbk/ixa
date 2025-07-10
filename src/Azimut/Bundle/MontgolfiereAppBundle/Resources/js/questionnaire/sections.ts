import {uniqueArray} from "./utils";
import {progressProgressBar} from "./progress-bar";

type SortingFactorCombination = {[key: number]: number};
declare const affectations: SortingFactorCombination[];

// Attach click event to previous and next buttons
document.querySelectorAll<HTMLElement>('[data-action=next],[data-action=previous]').forEach((el: HTMLElement) => {
    const targetSelector = el.dataset['target'];
    if(!targetSelector) {
        throw new Error('No target set for element with action='+el.dataset['action']);
    }
    const target = document.querySelector<HTMLElement>(targetSelector);
    if(!target) {
        throw new Error('Could not find target '+targetSelector+' for element with action='+el.dataset['action'])
    }

    let sections = 0;
    switch (el.dataset['action']) {
        case 'next':
            sections = 1;
            break;
        case 'previous':
            sections = -1;
            break;
        default:
            throw new Error('Logic exception');
    }

    el.addEventListener('click', (ev => {
        if(el.classList.contains('disabled')) {
            return;
        }

        if(false !== changeSection(sections, target, ev)) {
            progressProgressBar(sections);
        }
    }));
    el.addEventListener('keypress', function (ev: KeyboardEvent) {
        if(['Enter', 'NumpadEnter'].includes(ev.code)) {
            return;
        }
        this.dispatchEvent(new MouseEvent('click'));
    });
});

let nextTimeout: number|undefined;
// Handle the checked class for parent element and automatically change to next section
document.querySelectorAll<HTMLInputElement>('input[type=radio]').forEach((el: HTMLInputElement) => {
    el.addEventListener('click', () => {
        unselectUpcomingSortingFactors(el);

        if(nextTimeout) {
            clearTimeout(nextTimeout);
        }
        let timeout = 750;
        if (el.form!.classList.contains('no-animation')){
            timeout = 0;
        }
        nextTimeout = window.setTimeout(() => {
            if(false !== changeSection(1, document.querySelector<HTMLElement>(el.dataset['container']!)!)) {
                progressProgressBar(1);
            }
        }, timeout);
    });
});

// Start of helper functions

/**
 * "Scrolls" the container to {sections} sections. Returns false if scroll was prevented
 * @param sections Number of sections to scroll. Can be a positive number to scroll forward, or a negative number to scroll backward
 * @param container The container to scroll
 * @param event An optional event that will be prevented if passed
 */
function changeSection(sections: number, container: HTMLElement, event?: Event): undefined | false {
    if(window.getComputedStyle(container).display !== 'flex') {
        console.log('Container is not a flex container, doing nothing (were CSS styles disabled?)');
        return;
    }
    if (event) {
        event.preventDefault();
    }
    let marginLeft = container.style.marginLeft;

    let marginLeftNumber: number;
    if (marginLeft) {
        marginLeft = marginLeft.substr(0, marginLeft.length - 2);
        marginLeftNumber = parseInt(marginLeft);
    } else {
        marginLeftNumber = 0;
    }
    marginLeftNumber -= sections * 100;
    if (marginLeftNumber > 0) {
        return false;
    }

    const targetSectionNumber = -marginLeftNumber / 100;
    const targetSection = container.children[targetSectionNumber];
    const targetInput = targetSection.querySelector<HTMLInputElement>('input')!;
    const currentSectionNumber = targetSectionNumber - sections;
    const currentSection = container.children[currentSectionNumber];
    const currentInputs = currentSection.querySelectorAll<HTMLInputElement>('input')!;

    // Only check validity when going forward, not backward
    if(sections > 0) {
        for (const input of Array.from(currentInputs)) {
            if(false === input.reportValidity()) {
                return false;
            }
        }
    }

    currentSection.querySelectorAll<HTMLElement>("[tabindex='0']").forEach(el => {
        el.setAttribute('tabindex', '-10');
    });
    targetSection.querySelectorAll<HTMLElement>("[tabindex='-10']").forEach(el => {
        el.setAttribute('tabindex', '0');
    });

    const totalSections = (Array.from(container.children) as Array<HTMLElement>).filter((el: HTMLElement) => !el.dataset.hasOwnProperty('stepIgnore')).length;
    const isLast = currentSectionNumber === totalSections - 1;

    if(sections > 0 && isLast) {
        // If we're on the last section and we have a submit button on it, press it
        const submitButton = currentSection.querySelector<HTMLButtonElement>('input[type=submit]');
        if(submitButton) {
            submitButton.click();
        }
        return false;
    }
    container.parentElement!.dataset.changingSection = "true";
    setTimeout(() => {
        // Although application of scrollTop is immediate, the smooth scrolling take a few ms to finish
        container.parentElement!.dataset.changingSection = "false";
        container.classList.remove('no-animation');
    }, 1000);
    container.parentElement!.scrollTop = 0;
    container.style.marginLeft = marginLeftNumber + 'vw';
    currentSection.classList.remove('active');
    targetSection.classList.add('active');

    // Enable/disable buttons
    document.querySelectorAll<HTMLElement>('[data-disabled=first]').forEach((el: HTMLElement) => el.classList.toggle('disabled', marginLeftNumber === 0));
    document.querySelectorAll<HTMLElement>('[data-disabled=noskip-or-last]').forEach((el: HTMLElement) => {
        const formData = new FormData(targetInput.form!);
        const hasValue = formData.has(targetInput.name);

        el.classList.toggle('disabled', (targetInput.required && !hasValue) || isLast);
    });
    if(targetInput) {
        enableOrDisableSortingFactorValues(targetInput, sections === -1, container);
    }
}
window.changeSection = changeSection;

function enableOrDisableSortingFactorValues(targetInput: HTMLInputElement, isPrevious: boolean, container: HTMLElement) {
    const nameRegexp = new RegExp('^' + targetInput.form!.name + '\\[sorting_factor_(?<id>\\d+)\\]$');
    const nameRegexpResult = targetInput.name.match(nameRegexp);
    if (nameRegexpResult !== null) {
        const id = parseInt(nameRegexpResult.groups!.id);
        const currentSFValues = getSortingFactorValues(targetInput);
        const remainingAffectations = affectations.filter(affectation => {
            for (const key in currentSFValues) {
                // noinspection JSUnfilteredForInLoop
                if (parseInt(key) !== id && affectation.hasOwnProperty(key) && affectation[key] !== currentSFValues[key]) {
                    return false;
                }
            }
            return true;
        });
        const validChoices = remainingAffectations.map(value => value[id]).filter(uniqueArray);
        const parentContainer = targetInput.closest('[data-sorting-factor-id]')!;
        const inputs = parentContainer.querySelectorAll<HTMLInputElement>('input');
        inputs.forEach(value => {
            value.disabled = validChoices.indexOf(parseInt(value.value)) === -1;
            value.parentElement!.classList.toggle('disabled', validChoices.indexOf(parseInt(value.value)) === -1)
        });

        // Reorder values by pushing disabled values to the end and sorting by order
        (Array.from(parentContainer.children) as Array<HTMLElement>).sort((a: HTMLElement, b: HTMLElement) => {
            const aDisabled = a.querySelector('label')!.classList.contains('disabled');
            const bDisabled = b.querySelector('label')!.classList.contains('disabled');
            if(aDisabled && !bDisabled) {return 1;}
            if(bDisabled && !aDisabled) {return -1;}
            return parseInt(a.querySelector('input')!.dataset.order!)<parseInt(b.querySelector('input')!.dataset.order!)?-1:1;
        }).forEach(node => parentContainer.appendChild(node));

        //if only one answer available, auto select it
        if (validChoices.length == 1) {
            const uniqueInput = parentContainer.querySelector<HTMLInputElement>('input[id="'+ targetInput.form!.name +'_sorting_factor_'+ id +'_'+ validChoices[0] +'"]');
            if (uniqueInput) {
                // If we're going back, and we only have a single value possible, go back one time further
                if(isPrevious) {
                    changeSection(-1, container);
                }
                else {
                    //disable form animation
                    targetInput.form!.classList.add('no-animation');
                    uniqueInput.click();
                }
            }
        }
    }
}

/**
 * Returns the current selected combination. Some values might not be set yet
 */
function getSortingFactorValues(input: HTMLInputElement): SortingFactorCombination {
    const form = input.form!;
    let result: SortingFactorCombination = {};

    const nameRegexp = new RegExp('^' + form.name + '\\[sorting_factor_(?<id>\\d+)\\]$');
    const fd = new FormData(form);
    let stop = false; // Stop building combinations once we've found current input (when going back, don't consider upcoming values)
    fd.forEach(((value, key) => {
        if(stop) {
            return;
        }
        const regexResult = key.match(nameRegexp);
        if(regexResult === null) {
            return;
        }
        if(key === input.name) {
            stop = true;
        }
        result[parseInt(regexResult.groups!.id)] = parseInt(<string>value);
    }));

    return result;
}

function unselectUpcomingSortingFactors(el: HTMLInputElement) {
    if (el.name.indexOf('sorting_factor') > -1) {
        const sfInputs = el.form!.querySelectorAll<HTMLInputElement>('input[type=radio][name^="' + el.form!.name + '[sorting_factor_"]');
        let inputFound = false;
        sfInputs.forEach(input => {
            if (input == el) {
                inputFound = true;
            }
            if (!inputFound) {
                return;
            }
            if (input.name === el.name) {
                return;
            }
            input.checked = false;
            input.parentElement!.classList.remove('checked');
        });
    }
}
