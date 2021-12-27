import queryString from "query-string";
import axios from "axios";

var styles = `
body, html {
    height: 100%;
}
.idaas-popup{
    position: fixed;
    top: 0px;
    left: 0px;
    background-color: rgba(0,0,0,0.5);
}
.idaas-popup, .idaas-popup iframe {
    width: 100vw;
    height: 100vh;
    border-style: none;
}
`;

var styleSheet = document.createElement("style");
styleSheet.type = "text/css";
styleSheet.innerText = styles;

document.head.appendChild(styleSheet);

export default class Idaas {
  constructor({
    tenant,
    client_id,
    redirect_uri,
    post_logout_redirect_uri = null,
    authorization_endpoint = `oauth/authorize`,
    token_endpoint = `token`,
    end_session_endpoint = `oauth/logout`,
    style = {}
  }) {
    this.client_id = client_id;
    this.redirect_uri = redirect_uri;
    this.post_logout_redirect_uri = post_logout_redirect_uri;

    tenant = tenant.replace(/\/$/, "");

    this.login_endpoint = `${tenant}`;
    this.authorization_endpoint = `${tenant}/${authorization_endpoint}`;
    this.token_endpoint = `${tenant}/${token_endpoint}`;
    this.end_session_endpoint = `${tenant}/${end_session_endpoint}`;

    this.allowed_origins = [
      // Remove a trailing slash if any
      tenant
    ];

    this.checkContinue();
  }

  checkContinue() {
    let parsed = queryString.parse(document.location.hash);

    if (parsed.state) {
      this.login({
        state: parsed.state
      });
    }
  }

  /**
   * Receive authorization request response
   * @param {*} event
   */
  receiveMessage(
    event,
    allowed_origins,
    client_id,
    redirect_uri,
    popup,
    resolve,
    reject
  ) {
    if (event.data.type == "redirect") {
      //FIXME: not needed anymore?
      window.top.location = event.data.location;
    } else if (event.data.type == "authorization_response") {
      // TODO: this url depends on the tenant
      // TODO: check state
      if (event.data.response.error) {
        reject(event.data);
      } else {
        let state = window.sessionStorage.getItem("oauth_state");
        window.sessionStorage.removeItem("oauth_state");

        if (state !== event.data.response.state) {
          reject(event.data.response);
        } else {
          axios
            .post(this.token_endpoint, {
              client_id: client_id,
              grant_type: "authorization_code",
              code: event.data.response.code,
              redirect_uri: redirect_uri
            })
            .then(response => {
              // TODO: save id_token en access_token in local/sessionstorage
              // TODO: bij opnieuw aanroepen .login(), controleer geldigheid access_token (via jwt?), refresh zodra verlopen.
              resolve(response.data);
            })
            .catch(response => {
              reject(response.data);
            });
        }
      }
    } else if (event.data.type === "logout_response") {
      resolve(event.data);
    } else if (event.data.type === "get_style") {
      // postMessage with style
      let iframe = popup.querySelector("iframe");
      iframe.contentWindow.postMessage(
        {
          type: "set_style",
          style: {
            container_backgroundImage: "",
            container_backgroundColor: "rgba(0,0,0,0.5)"
          }
        },
        iframe.src
      );
    } else if (event.data.type === "close_popup") {
      reject("Closed");
    }
  }

  logout() {
    return new Promise((resolve, reject) => {
      const popup = document.createElement("div");
      const that = this;

      window.addEventListener(
        "message",
        function _message(event) {
          if (!that.allowed_origins.includes(event.origin)) return;

          that.receiveMessage(
            event,
            that.allowed_origins,
            that.client_id,
            that.redirect_uri,
            popup,
            data => {
              popup.parentNode.removeChild(popup);
              window.removeEventListener("message", _message);
              resolve(data);
            },
            data => {
              popup.parentNode.removeChild(popup);
              window.removeEventListener("message", _message);
              reject(data);
            }
          );
        },
        {
          passive: true
        }
      );

      popup.setAttribute("class", "idaas-popup");

      var iframe = document.createElement("iframe");
      iframe.setAttribute(
        "src",
        `${
          this.end_session_endpoint
        }?response_mode=web_message&post_logout_redirect_uri=${
          this.post_logout_redirect_uri ? this.post_logout_redirect_uri : ""
        }`
      );

      popup.appendChild(iframe);

      document.body.appendChild(popup);
    });
  }

  login({ acr_values = [], state = null } = {}) {
    return new Promise((resolve, reject) => {
      const popup = document.createElement("div");
      const that = this;

      window.addEventListener(
        "message",
        function _message(event) {
          if (!that.allowed_origins.includes(event.origin)) return;

          that.receiveMessage(
            event,
            that.allowed_origins,
            that.client_id,
            that.redirect_uri,
            popup,
            data => {
              popup.parentNode.removeChild(popup);
              window.removeEventListener("message", _message);
              resolve(data);
            },
            data => {
              popup.parentNode.removeChild(popup);
              window.removeEventListener("message", _message);
              reject(data);
            }
          );
        },
        {
          passive: true
        }
      );

      popup.setAttribute("class", "idaas-popup");

      var iframe = document.createElement("iframe");

      // When getting redirected back to the page
      if (state) {
        iframe.setAttribute("src", `${this.login_endpoint}/#state=${state}`);
      } else {
        state = Math.random()
          .toString(36)
          .slice(-5);
        window.sessionStorage.setItem("oauth_state", state);
        iframe.setAttribute(
          "src",
          `${
            this.authorization_endpoint
          }?display=popup&acr_values=${acr_values.join("+")}&client_id=${
            this.client_id
          }&nonce=12345&redirect_uri=${encodeURIComponent(
            this.redirect_uri
          )}&response_type=code&scope=openid&state=${encodeURIComponent(
            state
          )}&response_mode=web_message`
        );
      }

      // iframe.setAttribute('sandbox','allow-popups allow-scripts allow-same-origin allow-forms');
      iframe.setAttribute("allowTransparency", "true");

      popup.appendChild(iframe);

      document.body.appendChild(popup);
    });
  }
}

window.Idaas = Idaas;
