export default class API
{
    static TestMethod()
    {
        console.log('form API.js');
    }
    
    static BaseUrl = '/api';

    /**
     * Generic call to the backend API
     * @param method Request method: GET, POST, PUT, PATCH, DELETE
     * @param url Request URL, in the form /api{/url}
     * @param body Request payload, as object (will be JSON-ified)
     * param authHeader Should Authorization header be included? Will be only included if jwt token is found in redux store.
     */
    static Fetch(method, url, body = null)
    {
        const headers = new Headers();
        headers.append('Accept', 'application/json');
        if (body !== null) {
            headers.append('Content-Type', 'application/json');
        }

        /*if (authHeader) {
            const {isLoggedIn, jwt} = store.getState().auth;
            if (isLoggedIn) {
                headers.append('Authorization', 'Bearer ' + jwt);
            }
        }*/

        body = body === null ? null : JSON.stringify(body);

        return fetch(url, {
            method: method,
            headers: headers,
            body: body
        });
    }
}

API.Hyphenation = class
{
    static BaseUrl = API.BaseUrl + '/hyphenator';
    
    static SingleWord(word)
    {
        return API.Fetch('POST', `${this.BaseUrl}/singleWord?word=${word}`);
    }
    
    static Text(text)
    {
        return API.Fetch('POST', `${this.BaseUrl}/text?text=${text}`);
    }
}