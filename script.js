async function getIpInfo() {
    const URL = "https://ipapi.co/json/"
    const resp = await fetch(URL)
    const data = await resp.json()
    
    let ip_html = `${data.ip}<br>${data.city}, ${data.country_name}`

    document.querySelector("#ip-info").innerHTML = ip_html
}

// Informationen om nuvarande ställen fås från API. Om misslyckades ställs in "Helsinki" som default
async function getCityInfo() {
    const URL = "https://ipapi.co/json/"
    let userCity = ""

    try {
        const resp = await fetch(URL)
        const data = await resp.json()
        userCity = data.city
    } catch (error) {
        console.log(error)
        userCity = "Helsinki"
    }
    document.querySelector("#weather-city").innerHTML = userCity
    return userCity
}


async function getWeatherInfo(city) {
    const URL = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${config.weather_key}&units=metric`
    const resp = await fetch(URL)
    const data = await resp.json()
    
    document.querySelector("#weather-pic").innerHTML = `<img src=\"https://openweathermap.org/img/wn/${data.weather[0].icon}@2x.png\">`
    document.querySelector("#weather-info").innerHTML = `${data.main.temp}&deg;C, wind ${data.wind.speed} m/s, ${data.weather[0].description}`
}

async function getCurrencyInfo() {
    const URL = `https://api.exchangerate.host/latest`
    const resp = await fetch(URL)
    const data = await resp.json()

    let currency_html = `1 &euro; = ${data.rates.SEK.toFixed(2)} SEK<br>
                         1 &euro; = ${data.rates.USD.toFixed(2)} &#36;<br>
                         1 &euro; = ${data.rates.DKK.toFixed(2)} DKK`

    document.querySelector("#currency-info").innerHTML = currency_html
}

async function getCameraPicture() {
    const URL = "https://tie.digitraffic.fi/api/v1/data/camera-data"
    const resp = await fetch(URL)
    const data = await resp.json()

    const cameraN = Math.floor(Math.random() * data.cameraStations.length)
    const cameraP = Math.floor(Math.random() * data.cameraStations[cameraN].cameraPresets.length)
    const pic_url = data.cameraStations[cameraN].cameraPresets[cameraP].imageUrl
    
    let camera_html = `<img src="${pic_url}" class="img-fluid" alt="Vägbild">`
    document.querySelector("#camera-picture").innerHTML = camera_html
}

function initiateSettings() {
    const widgetKey = localStorage.getItem("widgetKey")
    const toDoKey = localStorage.getItem("toDoKey")
    if (widgetKey != null) {
        document.querySelector("#formWidgetAPI").setAttribute("value", widgetKey)
    }
    if (toDoKey != null) {
        document.querySelector("#formToDoAPI").setAttribute("value", toDoKey)
    }
}

function saveSettings() {
    const widgetKey = document.querySelector("#formWidgetAPI").value
    const toDoKey = document.querySelector("#formToDoAPI").value
    
    localStorage.setItem("widgetKey", widgetKey)
    localStorage.setItem("toDoKey", toDoKey)
}

getIpInfo()
getCityInfo().then(city => getWeatherInfo(city))
getCurrencyInfo()
getCameraPicture()
initiateSettings()

// Eventlisteners
document.querySelector("#refresh-picture").addEventListener("click", getCameraPicture)
document.querySelector("#submit-settings").addEventListener("click", saveSettings)