// Function
const gameBody = document.getElementById('game-body')
const btnSinglePLay = document.getElementById('btn-single-play')
const cardModes = document.getElementsByClassName('card-mode')
const levelInput = document.getElementById('level-input')

if (!userId) throw new Error('Please init "userId" variable !')

let room = {
    name: null,
    members: null
}

function hideElement(element) {
    element.classList.remove('show')
    element.classList.add('hide')
    setTimeout(() => {
        element.style.display = 'none'
    }, 1000)
}

function showElement(element) {
    element.classList.remove('hide')
    element.classList.add('show')
    setTimeout(() => {
        element.style.display = 'block'
    }, 1000)
}

function leave() {
    gameBody.innerHTML = ''
    for (let cardModeKey of cardModes) {
        showElement(cardModeKey)
    }
    hideElement(gameBody)
}

let guess = []
let imgs = [];
let guessIndex = 0

function percent(unit, max) {
    return Math.round(unit * 100 / max);
}

const loadImage = src =>
    new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.crossOrigin = 'anonymous'
        img.src = src;
    });

async function loadExtra() {
    const subarray = guess.slice(3).map(iso => 'https://countryflagsapi.com/svg/' + iso);
    for (let i = 0; i < subarray.length; i += 3) {
        await Promise.all([subarray[i], subarray[i+1], subarray[i+2]].map(loadImage)).then(images => {
            images.forEach((image) =>
                imgs.push(image)
            );
        });
    }
}

async function loadingGame() {
    const imagesString = [];
    if (guess.length >= 3) {
        imagesString.push('https://countryflagsapi.com/svg/' + guess[0]);
        imagesString.push('https://countryflagsapi.com/svg/' + guess[1]);
        imagesString.push('https://countryflagsapi.com/svg/' + guess[2]);
        await Promise.all(imagesString.map(loadImage)).then(images => {
            images.forEach((image) =>
                imgs.push(image)
            );
        });

        if (guess.length > 3) {
            loadExtra();
        }
        play()
    }
}

function play() {
    for (let cardModeKey of cardModes) {
        hideElement(cardModeKey)
    }
    showElement(gameBody)
    gameBody.innerHTML = `<div class="col-md-12"><div class="h-100 p-5 text-bg-secondary rounded-3"><div class="row align-items-md-stretch mb-4"><button id="btn-quit" type="button" class="btn btn-outline-light my-3">Quit Game</button><div id="game-main" class="d-flex flex-column  align-items-center w-100"></div></div></div></div>`
    document.getElementById('game-main').innerHTML = `
                    <div class="progress w-100 m-2" role="progressbar" aria-label="Example with label" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                      <div id="progress-content" class="progress-bar" style="width: ${percent(1, guess.length)}%">1/${guess.length}</div>
                    </div>
                    <img id="game-flag-img-guess" src="${imgs[0].src}" class="img-fluid rounded w-50" crossorigin="anonymous" alt="flag">
                    <form id="guess-form" class="d-flex flex-column">
                        <input id="game-input-guess" class="form-control my-3" name="guess" placeholder="Guess the country...">
                        <button type="submit" class="btn btn-dark">Submit</button>
                    </form>`
    document.getElementById('guess-form').onsubmit = (event) => {
        event.preventDefault()
        guessEmit(guess[guessIndex], document.getElementById('game-input-guess').value);

    }
    document.getElementById('btn-quit').onclick = () => leaveRoomEmit();
}

function clearGameMain() {
    document.getElementById('game-main').innerHTML = '';
}

// WebSocket

let socket = new WebSocket("ws://localhost:9000");

socket.onopen = () => {
    console.log("FindTheFlagServer : CONNECTED");
}

socket.onmessage = async (event) => {
    try {
        const response = JSON.parse(event.data);
        console.log(response)
        switch (response.event) {
            case '@GameStart':
                guess = [];
                guess.push(...response.guess)
                room = { name: response.room }
                await loadingGame();
                break;
            case '@RoomLeaved':
                leave()
                break;
            case '@CountryGuess':
                guessEvent(response.is_correct)
                break;
            case '@GameFinished':
                finishEvent(response);
                break;
        }
    } catch (e) {
        // Catch any errors
    }
};

socket.onclose = () => {
    console.warn('Connection lost, retry in 30 seconds...')
    setTimeout(() => {
        const closeFunction = socket.onclose;
        const openFunction = socket.onopen;
        const messageFunction = socket.onmessage;
        socket = null;
        socket = new WebSocket("ws://localhost:9000");
        socket.onclose = closeFunction;
        socket.onopen = openFunction;
        socket.onmessage = messageFunction
    }, 30000)
}

// Input

btnSinglePLay.onclick = () => {
    let levelId = +levelInput?.value.split('-')[0]
    let diff = +levelInput?.value.split('-')[1] ?? 1;
    if (!diff || typeof diff !== 'number' || diff < 1) {
        diff = 1
    }
    else if (diff > 25) {
        diff = 25
    }

    const request = {
        event: '@StartGameSingle',
        user_id: userId,
        level_id: levelId,
        difficulty: diff
    }
    socket.send(JSON.stringify(request));
}

function leaveRoomEmit() {
    const request = {
        event: '@LeaveRoom',
        room_name: room.name,
        user_id: userId
    }
    socket.send(JSON.stringify(request));
}

function guessEmit(iso, response) {
    const request = {
        event: '@GuessCountry',
        room_name: room.name,
        user_id: userId,
        response: {
            iso,
            country_name: response,
        },
    }
    socket.send(JSON.stringify(request));
}

function guessEvent(isCorrect = false) {
    const img = document.getElementById('game-flag-img-guess');
    const progress = document.getElementById('progress-content');
    if (isCorrect) {
        img.classList.add('guess-true');
        progress.classList.add('guess-true');
    } else {
        img.classList.add('guess-false');
        progress.classList.add('guess-false');
    }
    setTimeout(() => {
        img.classList.remove('guess-false');
        img.classList.remove('guess-true');
        progress.classList.remove('guess-false');
        progress.classList.remove('guess-true');
    }, 600)

    guessIndex += 1
    progress.setAttribute('style', `width: ${percent(guessIndex+1, guess.length)}%`);
    progress.innerHTML = `${guessIndex+1}/${guess.length}`;
    document.getElementById('game-input-guess').value = '';
    if (guess[guessIndex]) {
        img.setAttribute('src', `${imgs[guessIndex].src}`)
    } else {
        finish();
        guess = []
        guessIndex = 0
        clearGameMain();
    }
}

function finish() {
    const request = {
        event: '@FinishGame',
        room_name: room.name,
        user_id: userId,
        game_time: 1000,
    }
    socket.send(JSON.stringify(request));
}

function finishEvent(response) {
    let answerDivs = '';
    const adminBadge = response.user.isAdmin ? '<span title="Admin"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-globe-americas" viewBox="0 0 16 16"><path d="M8 0a8 8 0 1 0 0 16A8 8 0 0 0 8 0ZM2.04 4.326c.325 1.329 2.532 2.54 3.717 3.19.48.263.793.434.743.484-.08.08-.162.158-.242.234-.416.396-.787.749-.758 1.266.035.634.618.824 1.214 1.017.577.188 1.168.38 1.286.983.082.417-.075.988-.22 1.52-.215.782-.406 1.48.22 1.48 1.5-.5 3.798-3.186 4-5 .138-1.243-2-2-3.5-2.5-.478-.16-.755.081-.99.284-.172.15-.322.279-.51.216-.445-.148-2.5-2-1.5-2.5.78-.39.952-.171 1.227.182.078.099.163.208.273.318.609.304.662-.132.723-.633.039-.322.081-.671.277-.867.434-.434 1.265-.791 2.028-1.12.712-.306 1.365-.587 1.579-.88A7 7 0 1 1 2.04 4.327Z"/></svg></span>' : '';
    const verifiedBadge = response.user.isVerified ? '<span title="Verified User"><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#00DB87" class="bi bi-patch-check-fill" viewBox="0 0 16 16"> <path d="M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01-.622-.636zm.287 5.984-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708.708z"/></svg></span>' : '';
    response.game.answers.forEach((answer, index) => {
        answerDivs += `
            <div class="d-flex flex-row align-items-center justify-content-start py-2">
                <span class="fs-4 px-3 text-nowrap">n° ${index+1}</span>
                <img src="https://countryflagsapi.com/svg/${answer.iso}" alt="flag-${answer.iso}" class="img-fluid rounded object-fit-contain" width="100" crossorigin="anonymous">
                <div class="p-3">
                    <p class="m-0" style="width: 350px"><strong>Correct : </strong>${answer.correct_answer}</p>
                    <p class="m-0" style="color: ${answer.is_correct ? 'green' : 'red'}"><strong>Response : </strong>${answer.user_answer}</p>
                </div>
            </div>
        `
    })
    document.getElementById('game-main').innerHTML = `
        <div class="row align-items-md-stretch">
            <div class="col-md-6">
                <div class="card-mode h-100 p-5 text-bg-dark rounded-3">
                    <h2 class="d-flex flex-row align-items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" fill="currentColor" class="bi bi-geo-alt-fill px-2" viewBox="0 0 16 16">
                          <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                        </svg>
                        Answers
                    </h2>
                    <div class="d-flex flex-column">
                        ${answerDivs}
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card-mode h-100 p-5 text-bg-dark rounded-3">
                    <h2>
                        <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" fill="currentColor" class="bi bi-person-lines-fill px-2" viewBox="0 0 16 16">
                          <path d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5zm.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1h-4zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2zm0 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2z"/>
                        </svg>
                        User Score
                    </h2>
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        <span class="fs-3 mb-3"><strong>Score : </strong>${response.game.score}</span>
                        <img src="${response.user.img}" alt="img-user-${response.user.id}" class="img-fluid rounded-circle object-fit-cover" width="150" />
                        <span class="fs-4">${response.user.name} ${adminBadge} ${verifiedBadge}</span>
                        <ul style="list-style: none" class="my-3">
                            <li class="fs-5 m-2">
                                <span><strong>XP : </strong>${response.user.xp}</span>
                                <small style="color: green"> + ${response.game.rewards.xp}</small>
                            </li>
                            <li class="fs-5 m-2">
                                <span><strong>Coins : </strong>${response.user.coins}</span>
                                <small style="color: green"> + ${response.game.rewards.coins}</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `;
}