interface Translator {
    trans(id: string, parameters?: {[key: string]: string;}, domain?: string, locale?: string): string;
}

declare var Translator: Translator;
