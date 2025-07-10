export function progressProgressBar(steps: number): void {
    const progressBar = document.querySelector('.workcare-progress-container');
    if (!progressBar) {
        return;
    }

    const bar = progressBar.querySelector<HTMLElement>('.workcare-progress-bar');
    if (!bar) {
        throw new Error('Malformed progress bar, missing .workcare-progress-bar');
    }
    let step = parseInt(bar.dataset['currentStep']!);
    step += steps;
    bar.dataset['currentStep'] = step + "";

    const sum = (accumulator: number, currentValue: number) => accumulator + currentValue;

    const stepsSize = JSON.parse(bar.dataset['stepsSize']!);
    const progress = stepsSize.slice(0, step).reduce(sum, 0);
    const progressPc = Math.round(progress * 100 / parseInt(bar.dataset['steps']!));
    bar.setAttribute('aria-valuenow', progressPc + "");
    progressBar.querySelector('.workcare-progress-text')!.textContent = progressPc + "%";

    bar.querySelector<HTMLElement>('.active')!.style.width = Math.round(progress * 100 / stepsSize.reduce(sum, 0)) + "%";
}
