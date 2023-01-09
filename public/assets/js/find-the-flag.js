// Function
const gameBody = document.getElementById('game-body')
const btnSinglePLay = document.getElementById('btn-single-play')
const cardModes = document.getElementsByClassName('card-mode')
const levelInput = document.getElementById('level-input')

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

function play() {
    for (let cardModeKey of cardModes) {
        hideElement(cardModeKey)
    }
    showElement(gameBody)
    gameBody.innerHTML = `<div class="col-md-12"><div class="h-100 p-5 text-bg-secondary rounded-3"><div class="row align-items-md-stretch mb-4"><button type="button" class="btn btn-outline-light my-3" id="btn-quit">Quit Game</button><div id="game-main" class="d-flex flex-column  align-items-center w-100"></div></div></div></div>`
    document.getElementById('game-main').innerHTML = `<img id="game-flag-img-guess" src="https://countryflagsapi.com/svg/${guess[0]}" class="img-fluid rounded px-5 w-50" crossorigin="anonymous" alt="flag">
                    <form id="guess-form" class="d-flex flex-column">
                        <input id="game-input-guess" class="form-control my-3" name="guess" placeholder="Guess the country...">
                        <button type="submit" class="btn btn-dark">Submit</button>
                    </form>`
    document.getElementById('guess-form').onsubmit = (event) => {
        event.preventDefault()
        // TODO: EMIT GUESS
        guessIndex += 1
        responseArray.push(document.getElementById('game-input-guess').value)
        document.getElementById('game-input-guess').value = '';
        if (guess[guessIndex]) {
            document.getElementById('game-flag-img-guess').setAttribute('src', `https://countryflagsapi.com/svg/${guess[guessIndex]}`)
        } else {
            // TODO : EMIT FINISH
            console.log(responseArray)
            guess = []
            guessIndex = 0
            responseArray = []
            leave()
        }
    }
    document.getElementById('btn-quit').onclick = () => {
        leave()
    }
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
                guess.push(...response.guess)
                play()
                break;
            // case '@Messages':
            //     addMessages(response.response)
            //     break;
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
    let diff = levelInput?.value ?? 1;
    if (!diff || typeof diff !== 'number' || diff < 1) {
        diff = 1
    }
    else if (diff > 25) {
        diff = 25
    }

    const request = {
        event: '@StartGameSingle',
        difficulty: diff
    }
    console.log(request)
    socket.send(JSON.stringify(request));
}