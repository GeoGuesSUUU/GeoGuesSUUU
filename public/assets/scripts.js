const addEffectFormToCollection = (e) => {
    const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);

    const item = document.createElement('div');
    item.classList.add('element-effect');
    item.classList.add('d-flex');

    item.innerHTML = collectionHolder
        .dataset
        .prototype
        .replace(
            /__name__/g,
            collectionHolder.dataset.index
        );

    item.firstElementChild.classList.add('d-flex', 'w-100', 'mb-3', 'effect');
    item.firstElementChild.firstElementChild.classList.add('w-100', 'form-floating', 'me-1');
    item.firstElementChild.firstElementChild.append(...Array.from(item.firstElementChild.firstElementChild.childNodes).reverse());
    item.firstElementChild.firstElementChild.firstElementChild.classList.add('form-control');
    item.firstElementChild.firstElementChild.firstElementChild.setAttribute('placeholder', 'value');
    item.firstElementChild.lastElementChild.classList.add('w-100', 'me-1');
    item.firstElementChild.lastElementChild.removeChild(item.firstElementChild.lastElementChild.firstChild);
    item.firstElementChild.lastElementChild.lastElementChild.classList.add('form-select');

    addEffectFormDeleteLink(item.firstElementChild);

    collectionHolder.appendChild(item);

    collectionHolder.dataset.index++;
};

const addEffectFormDeleteLink = (item) => {
    const removeFormButton = document.createElement('button');
    removeFormButton.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                        <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"></path>
                                      </svg>`;

    removeFormButton.classList.add('btn', 'btn-danger');

    removeFormButton.style.width = '40px';
    removeFormButton.style.height = '40px';
    removeFormButton.style.margin = 'auto';

    item.append(removeFormButton);

    removeFormButton.addEventListener('click', (e) => {
        e.preventDefault();
        item.remove();
    });
}

document
    .querySelectorAll('.add_item_link')
    .forEach(btn => {
        btn.addEventListener("click", addEffectFormToCollection)
    });

document
    .querySelectorAll('div.effect')
    .forEach((tag) => {
        addEffectFormDeleteLink(tag)
    })