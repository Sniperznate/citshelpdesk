const express = require("express");
const cors = require("cors");
const supertokens = require("supertokens-node");
const { middleware } = require("supertokens-node/framework/express");
const Session = require("supertokens-node/recipe/session");
const EmailPassword = require("supertokens-node/recipe/emailpassword");

supertokens.init({
    framework: "express",
    supertokens: {
        connectionURI: "https://try.supertokens.com",  // Use self-hosted Supertokens URL later
    },
    appInfo: {
        appName: "MyApp",
        apiDomain: "http://localhost:3000",  // Change to Clever Cloud domain later
        websiteDomain: "https://app-a241f7f9-0b4d-473f-bccf-5bd8051b932f.cleverapps.io/",  // Your Android API URL
    },
    recipeList: [
        EmailPassword.init(),
        Session.init(),
    ],
});

const app = express();
app.use(cors({ origin: websiteDomain, credentials: true }));
app.use(express.json());
app.use(middleware());

app.listen(3000, () => {
    console.log("Server running on " + websiteDomain);
});
