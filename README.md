## Tree Polygon Editing Demo

This project started as an internal crowdsourcing tool for refining tree crown polygons. Operators on a commercial micro-tasking platform received pregenerated outlines, adjusted them on top of high-resolution imagery, and submitted their edits for quality assurance and research. Each session produced three artifacts: the final polygon coordinates, metadata about the worker assignment, and a detailed timeline of mouse/keyboard actions for replay.

### Production workflow

-   Workers arrived with campaign-specific query parameters
-   The backend reserved the next available job, wrote the interim files, and captured the action log
-   Administrators inspected the results via a private dashboard that replays every action in real time

### Demo mode

-   Visitors are automatically "qualified" and receive a random job from the static `jobs/` folder
-   Edited polygons never leave the browser; submitting stores a short recap in `sessionStorage` and redirects to the summary screen
-   The public log library (`userLogsView.php`) surfaces a curated set of genuine historical sessions for replay, while the original folders (`results/`, `user_info/`, `user_logs/`) stay untouched

This deployment is used to showcase the interface, highlight real editing sessions and explain the research goals without needing a live backend.
