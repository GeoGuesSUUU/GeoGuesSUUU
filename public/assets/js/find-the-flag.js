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
let guessIndex = 0
let responseArray = []

function percent(unit, max) {
    return Math.round(unit * 100 / max);
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
                    <img id="game-flag-img-guess" src="https://countryflagsapi.com/svg/${guess[0]}" class="img-fluid rounded w-50" crossorigin="anonymous" alt="flag">
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

// WebSocket

let socket = new WebSocket("ws://localhost:9000");

socket.onopen = () => {
    console.log("FindTheFlagServer : CONNECTED");
}

socket.onmessage = (event) => {
    try {
        const response = JSON.parse(event.data);
        console.log(response)
        switch (response.event) {
            case '@GameStart':
                guess = [];
                guess.push(...response.guess)
                room = { name: response.room }
                play()
                break;
            case '@RoomLeaved':
                leave()
                break;
            case '@CountryGuess':
                guessEvent(response.is_correct)
                break;
            // case '@ConnectionCount':
            //     updateOnline(response.response.count)
            //     break;
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
        socket = new WebSocket("ws://localhost:8001");
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
    console.log(request)
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
    responseArray.push(document.getElementById('game-input-guess').value)
    document.getElementById('game-input-guess').value = '';
    if (guess[guessIndex]) {
        img.setAttribute('src', `https://countryflagsapi.com/svg/${guess[guessIndex]}`)
    } else {
        // TODO : EMIT FINISH
        console.log(responseArray)
        guess = []
        guessIndex = 0
        responseArray = []
        leave()
    }
}