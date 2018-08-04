/**
 * @return {Promise<Object>}
 */
export const getLoginStatus = function (): Promise<facebook.AuthResponse> {
    return new Promise((resolve: Function) => {
        FB.getLoginStatus((response: facebook.AuthResponse) => {
            resolve(response);
        });
    });
};

/**
 * @return {Promise<Object>}
 */
export const login = function (): Promise<facebook.AuthResponse> {
    return new Promise((resolve: Function, reject: Function) => {
        FB.login((response: facebook.AuthResponse) => {
            if (response.authResponse) {
                resolve(response);
            } else {
                reject(response);
            }
        });
    });
};

/**
 * @return {Promise<Object>}
 */
export const logout = function (): Promise<facebook.AuthResponse> {
    return new Promise((resolve: Function, reject: Function) => {
        FB.logout((response: facebook.AuthResponse) => {
            if (response.authResponse) {
                resolve(response);
            } else {
                reject(response);
            }
        });
    });
};
