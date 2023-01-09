// WebSocket

let socket = new WebSocket("ws://localhost:9000");

socket.onopen = () => {
    console.log("FindTheFlagServer : CONNECTED");
    // const request = {
    //     event: '@GetMessages'
    // };
    // socket.send(JSON.stringify(request));
}

socket.onmessage = (event) => {
    try {
        const response = JSON.parse(event.data);
        switch (response.event) {
            // case '@Message':
            //     addMessage(response.response);
            //     break;
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