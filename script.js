async function getIpInfo(URL) {
    const resp = await fetch(URL)
    const data = await resp.json()
    
    let ip_html = `${data.ip}<br>${data.city}, ${data.country_name}`

    document.querySelector("#ip-info").innerHTML = ip_html
}

// Informationen om nuvarande ställen fås från API. Om misslyckades ställs in "Helsinki" som default
async function getCityInfo(URL) {
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


async function getWeatherInfo(URL, key, city) {
    const local_URL = `${URL}?q=${city}&appid=${key}&units=metric`
    const resp = await fetch(local_URL)
    const data = await resp.json()
    if (data.cod === 401) {
        console.log("Wrong API key")
        document.querySelector("#weather-info").innerHTML = "Fel API nyckel"
    } else {
        document.querySelector("#weather-pic").innerHTML = `<img src=\"https://openweathermap.org/img/wn/${data.weather[0].icon}@2x.png\">`
        document.querySelector("#weather-info").innerHTML = `${data.main.temp}&deg;C, wind ${data.wind.speed} m/s, ${data.weather[0].description}`
    }
    
}

async function getCurrencyInfo(URL) {
    const resp = await fetch(URL)
    const data = await resp.json()

    let currency_html = `1 &euro; = ${data.rates.SEK.toFixed(2)} SEK<br>
                         1 &euro; = ${data.rates.USD.toFixed(2)} &#36;<br>
                         1 &euro; = ${data.rates.DKK.toFixed(2)} DKK`

    document.querySelector("#currency-info").innerHTML = currency_html
}

async function getCameraPicture(URL) {
    const resp = await fetch(URL)
    const data = await resp.json()

    const cameraN = Math.floor(Math.random() * data.cameraStations.length)
    const cameraP = Math.floor(Math.random() * data.cameraStations[cameraN].cameraPresets.length)
    const pic_url = data.cameraStations[cameraN].cameraPresets[cameraP].imageUrl
    
    let camera_html = `<img src="${pic_url}" class="img-fluid" alt="Vägbild">`
    document.querySelector("#camera-picture").innerHTML = camera_html
}

function initiateSettings() {
    const apiKey = localStorage.getItem("apiKey")
    if (apiKey != null) {
        document.querySelector("#formWidgetAPI").setAttribute("value", apiKey)
    }
}

function saveSettings() {
    const apiKey = document.querySelector("#formWidgetAPI").value
    localStorage.setItem("apiKey", apiKey)
    location.reload();
}

async function getUserData() {
    const key = localStorage.getItem("apiKey")
    const URL = `https://cgi.arcada.fi/~popovmik/WDBoCMS/wdbcms22-projekt-1-unholy-overexert/api/?key=${key}`
    const resp = await fetch(URL)
    const data = await resp.json()

    if (data.error != "invalid api key") {
        const widgetsObj = JSON.parse(data[0].widgets)
        const ip_url = widgetsObj.ip.url
        const weather_url = widgetsObj.weather.url
        const weather_key = widgetsObj.weather.key
        const currency_url = widgetsObj.currency.url
        const picture_url = widgetsObj.picture.url

        getIpInfo(ip_url)
        getCityInfo(ip_url).then(city => getWeatherInfo(weather_url, weather_key, city))
        getCurrencyInfo(currency_url)
        getCameraPicture(picture_url)
    } else {
        window.alert("Fel API nyckel")
    }
}


initiateSettings()
getUserData()

// Eventlisteners
document.querySelector("#button-refresh").addEventListener("click", getUserData)
document.querySelector("#submit-settings").addEventListener("click", saveSettings)