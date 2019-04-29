import {
    elements,
    jQueryElements,
    elementStrings
} from './base';

export function initSortableItems() {
    jQueryElements.sortableUl.sortable({
        'tolerance': 'intersect',
        'cursor': 'pointer',
        'items': '> li',
        'axis': 'y',
        'placeholder': 'placeholder',
        'opacity': 0.7
    });
}

export function updateObjectPositionValue(e, ui) {

    Array.from(elements.sortableLi).forEach(el => {
        let positionContext = el.parentElement;
        let newPosition = Array.from(positionContext.children).indexOf(el) + 1;

        if (positionContext.classList.contains('hierarchy')) {
            el.querySelector(elementStrings.inputHierarchyPosition).value = newPosition;

        } else if (positionContext.classList.contains('apex')) {
            el.querySelector(elementStrings.inputApexPosition).value = newPosition;
        }
    })
}