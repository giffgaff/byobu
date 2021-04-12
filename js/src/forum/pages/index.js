import routes from "./routes";
import SetupPrivateDiscussionsPage from "./SetupPrivateDiscussionsPage";

export default (app) => {
    routes(app);
    SetupPrivateDiscussionsPage(app);
}
