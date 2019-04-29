import fadeOut from '../ui/fadeOut';

export default () => {
    const flashMessages = document.getElementsByClassName('flashMessageFadeOut');

    Array.from(flashMessages).forEach((element) => {

        element.addEventListener('click', () => {
            fadeOut(element);
        });
    });
}