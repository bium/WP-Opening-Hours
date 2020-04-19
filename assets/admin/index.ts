import { render, h } from "preact";
import RootComponent from "./root-component";

import "./legacy/ExtendedSettings";
import "./legacy/Holidays";
import "./legacy/IrregularOpenings";
import "./legacy/OpSet";
import "./legacy/Periods";
import "./legacy/ShortcodeBuilder";

import "./styles/index.scss";

render(h(RootComponent, {}), document.getElementById("root"));
