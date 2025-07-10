export interface Segment {
    id: number;
    locale: string;
    name: string;
    disabled: boolean;
    steps: Step[];
    readonly hasParticipations: boolean;
}

export enum StepType {divider = "divider", item = "item", question = "question"}

export interface Step {
    id: number;
    position: number;
    type: StepType;
    theme?: Theme;
    item?: Item;
    question?: Question;
}

export interface Theme {
    id: number;
    name: TranslatableString;
    items: Item[];
}

export interface Item {
    id: number;
    name: TranslatableString;
}

export interface Question {
    id: number;
    label: string;
    question: string;
    description: string;
    leftLabel: string;
    centerLabel: string;
    rightLabel: string;
    type: QuestionType;
    canBeSkipped: boolean;
    item?: Item;
    tags: QuestionTag[];
}

export enum QuestionType {
    SLIDER_VALUE = 0,
    OPEN = 1,
    TRUE_FALSE = 2,
    CHOICES_MULTIPLES = 3,
    CHOICES_UNIQUE = 4,
    SATISFACTION_GAUGE = 5,
}

export interface QuestionTag {
    id: number;
    name: string;
    color: string;
}

export interface TranslatableString {
    [key: string]: string;
}

export interface Campaign {
    id: number;
    name: string;
    allowedLanguages: string[];
}
