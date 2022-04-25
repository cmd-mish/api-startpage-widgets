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
    console.log(data)

    let currency_html = `1 &euro; = ${data.rates.SEK.toFixed(2)} SEK<br>
                         1 &euro; = ${data.rates.USD.toFixed(2)} &#36;<br>
                         1 &euro; = ${data.rates.DKK.toFixed(2)} DKK`

    document.querySelector("#currency-info").innerHTML = currency_html
}

getIpInfo()
getCityInfo().then(city => getWeatherInfo(city))
getCurrencyInfo()