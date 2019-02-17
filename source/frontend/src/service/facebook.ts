export const getLoginStatus = function(): Promise<facebook.StatusResponse> {
  return new Promise(resolve => {
    FB.getLoginStatus(response => {
      resolve(response);
    });
  });
};

export const login = function(): Promise<facebook.StatusResponse> {
  return new Promise((resolve, reject) => {
    FB.login(response => {
      if (response.authResponse) {
        resolve(response);
      } else {
        reject(response);
      }
    });
  });
};

export const logout = function(): Promise<facebook.StatusResponse> {
  return new Promise((resolve, reject) => {
    FB.logout(response => {
      if (response.authResponse) {
        resolve(response);
      } else {
        reject(response);
      }
    });
  });
};
