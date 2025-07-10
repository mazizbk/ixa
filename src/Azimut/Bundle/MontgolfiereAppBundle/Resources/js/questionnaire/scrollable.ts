document.querySelectorAll<HTMLElement>('.scrollable').forEach((scrollable: HTMLElement) => {
    const buttonsContainer = scrollable.parentElement!.querySelector('.scroll-buttons')!;
    const buttons = buttonsContainer.children;

    scrollable.addEventListener('scroll', () => {
        const scrollPc = (scrollable.scrollLeft + scrollable.clientWidth)/scrollable.scrollWidth;
        let buttonFound = false;
        for (let i = 0; i < buttons.length; i++) {
            const button = buttons[i];
            const buttonPc = (i+1)/buttons.length;
            button.classList.toggle('active', buttonPc >= scrollPc && !buttonFound);
            if(!buttonFound && buttonPc >= scrollPc) {
                buttonFound = true;
            }
        }
    });
});
