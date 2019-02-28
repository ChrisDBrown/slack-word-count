# Slack Word Count

Very quick tool to see who uses certain terms the most in your slack workspaces.

I'm not suggesting you use this to find the sweariest people, but that's definitely a thing you could use it for.

## Some Assembly Required

This was thrown together on a lunch break, so the code is _not good_. There's no attempt at error handling, and rate limiting is basically a "fingers crossed you're not hitting it" approach.

Still, setup is pretty quick:

1) Create an app at https://api.slack.com/apps
2) Add the Client ID and Client Secret for the app as `OAUTH_SLACK_ID` and `OAUTH_SLACK_SECRET` values in your relevant `.env` file (likely `.env.local`)
3) Under 'OAuth & Permissions' in your app settings add the url for the `slack_check` route (likely `http://127.0.0.1:8000/slack/check`)
4) Run with `bin/console server:run` and hope it works

Good luck!

### Caveats

- looks bad
- lots of exciting detail completely ignored in the results template
    - dig into the `user` object in the template for a fun time
- limits search to 500 results
    - see `ResultsController::getMessagesForQuery`
- only shows messages in public channels from (mostly) non-bots
    - see `continue` statement in `ResultsController::searchForTerm`
