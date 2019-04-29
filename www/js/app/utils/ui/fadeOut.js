export default (element) => {

    const fadeEffect = setInterval(function () {

        if (!element.style.opacity) {
            element.style.opacity = 1;
        }
        if (element.style.opacity > 0) {
            element.style.opacity -= 0.01;
        } else {
            element.style.height = 0;
            clearInterval(fadeEffect);
        }
    }, 20);
};