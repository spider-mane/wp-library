// import MetaBox from './models/MetaBox';
// import * as metaBoxView from './views/metaBoxView';
import * as sortableObjectView from './views/sortableObjectView';
import {
    elements,
    jQueryElements,
    wpPages
} from "./views/base";


if (window.pagenow === wpPages.sortObjects || window.pagenow === wpPages.sortPosts) {
    window.addEventListener('DOMContentLoaded', sortableObjectView.initSortableItems);
    jQueryElements.sortableUl.on('sortupdate', sortableObjectView.updateObjectPositionValue);
}