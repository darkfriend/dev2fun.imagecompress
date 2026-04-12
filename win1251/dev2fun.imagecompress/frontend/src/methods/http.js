import axios from 'axios'
import { configMain } from './configMain'

const http = axios.create({
    // baseURL: process.env.VUE_APP_ROOT_API,
    baseURL: configMain.url(),
    timeout: 60000,
    // headers: {
    //     'Content-Type': 'application/json',
    //     // 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    // },
    // transformRequest: (data, headers) => {
    //     // console.log(data);
    //     if (data) data = stringify(data);
    //     return data;
    // }
})

http.interceptors.request.use(
    (config) => {

        // if (config.method === 'post') {
        //     config.params = Object.assign({}, config.params, {sessid: BX.bitrix_sessid()})
        // }
        config.params = Object.assign({}, config.params, {sessid: BX.bitrix_sessid()})

        return config
    },
    (error) => {
        if (configMain.debug) {
            console.log(error)
        }
        return Promise.reject(error)
    },
)

http.interceptors.response.use(
    (response) => {
        const res = response.data
        if (configMain.debug) {
            console.log(res)
        }
        return res
    },
    (error) => {
        if (configMain.debug) {
            console.log(`err: ${error}`) // for debug
        }
        return Promise.reject(error)
    },
)

export default http
