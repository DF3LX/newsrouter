--
-- PostgreSQL database dump
--

-- Dumped from database version 12.12 (Debian 12.12-1.pgdg100+1)
-- Dumped by pg_dump version 15.0 (Debian 15.0-1.pgdg100+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: public; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA public;


ALTER SCHEMA public OWNER TO postgres;

--
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'standard public schema';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: formatter; Type: TABLE; Schema: public; Owner: darcnews
--

CREATE TABLE public.formatter (
    id integer NOT NULL,
    name character varying NOT NULL,
    enabled boolean DEFAULT false NOT NULL,
    filterid integer,
    channelid integer,
    classname character varying(64) NOT NULL,
    parameters text
);


ALTER TABLE public.formatter OWNER TO darcnews;

--
-- Name: TABLE formatter; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON TABLE public.formatter IS 'Konfiguration der Formatter';


--
-- Name: COLUMN formatter.id; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.formatter.id IS 'ID des Formatters';


--
-- Name: COLUMN formatter.name; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.formatter.name IS 'Name des Formatters';


--
-- Name: COLUMN formatter.enabled; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.formatter.enabled IS 'Flag ob der Formatter aktiv ist';


--
-- Name: COLUMN formatter.filterid; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.formatter.filterid IS 'ID des Filters, der diesen Formatter triggert';


--
-- Name: COLUMN formatter.channelid; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.formatter.channelid IS 'ID des Channels, für den die Nachricht erzeugt wird';


--
-- Name: COLUMN formatter.classname; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.formatter.classname IS 'ClassName der Klasse, die für diesen Formatter verwendet wird';


--
-- Name: COLUMN formatter.parameters; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.formatter.parameters IS 'JSON mit Formatter-spezifischen Einstellungen';


--
-- Name: Formatter_id_seq; Type: SEQUENCE; Schema: public; Owner: darcnews
--

CREATE SEQUENCE public."Formatter_id_seq"
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public."Formatter_id_seq" OWNER TO darcnews;

--
-- Name: Formatter_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: darcnews
--

ALTER SEQUENCE public."Formatter_id_seq" OWNED BY public.formatter.id;


--
-- Name: _bystate; Type: TABLE; Schema: public; Owner: darcnews
--

CREATE TABLE public._bystate (
    id smallint NOT NULL,
    name character varying(64)
);


ALTER TABLE public._bystate OWNER TO darcnews;

--
-- Name: TABLE _bystate; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON TABLE public._bystate IS 'Werte für die Spalte State';


--
-- Name: COLUMN _bystate.id; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public._bystate.id IS 'ID des Wertes';


--
-- Name: COLUMN _bystate.name; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public._bystate.name IS 'Name des Wertes';


--
-- Name: _messagestate; Type: TABLE; Schema: public; Owner: darcnews
--

CREATE TABLE public._messagestate (
    id smallint NOT NULL,
    name character varying(64)
);


ALTER TABLE public._messagestate OWNER TO darcnews;

--
-- Name: TABLE _messagestate; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON TABLE public._messagestate IS 'Mögliche Werte für MessageState';


--
-- Name: COLUMN _messagestate.id; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public._messagestate.id IS 'ID des MessageState';


--
-- Name: COLUMN _messagestate.name; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public._messagestate.name IS 'Beschreibung';


--
-- Name: channel; Type: TABLE; Schema: public; Owner: darcnews
--

CREATE TABLE public.channel (
    id integer NOT NULL,
    name character varying(64) NOT NULL,
    enabled boolean DEFAULT false NOT NULL,
    classname character varying(64) NOT NULL,
    parameters text
);


ALTER TABLE public.channel OWNER TO darcnews;

--
-- Name: TABLE channel; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON TABLE public.channel IS 'Konfiguration der Ausgabekanäle';


--
-- Name: COLUMN channel.id; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.channel.id IS 'ID des Ausgabekanals';


--
-- Name: COLUMN channel.name; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.channel.name IS 'Name des Ausgabekanals';


--
-- Name: COLUMN channel.enabled; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.channel.enabled IS 'Flag ob der Ausgabekanal aktiv ist';


--
-- Name: COLUMN channel.classname; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.channel.classname IS 'ClassName der Klasse, die für diesen Ausgabekanal verwendet wird';


--
-- Name: COLUMN channel.parameters; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.channel.parameters IS 'JSON mit Ausgabekanal-spezifischen Einstellungen';


--
-- Name: channel_id_seq; Type: SEQUENCE; Schema: public; Owner: darcnews
--

CREATE SEQUENCE public.channel_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.channel_id_seq OWNER TO darcnews;

--
-- Name: channel_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: darcnews
--

ALTER SEQUENCE public.channel_id_seq OWNED BY public.channel.id;


--
-- Name: filter; Type: TABLE; Schema: public; Owner: darcnews
--

CREATE TABLE public.filter (
    id integer NOT NULL,
    name character varying(64) NOT NULL,
    enabled boolean DEFAULT false NOT NULL,
    classname character varying(64) NOT NULL,
    parameters text
);


ALTER TABLE public.filter OWNER TO darcnews;

--
-- Name: TABLE filter; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON TABLE public.filter IS 'Konfiguration der Filter';


--
-- Name: COLUMN filter.id; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.filter.id IS 'ID des Filters';


--
-- Name: COLUMN filter.name; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.filter.name IS 'Name des Filters';


--
-- Name: COLUMN filter.enabled; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.filter.enabled IS 'Flag, ob der Filter aktiv ist';


--
-- Name: COLUMN filter.classname; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.filter.classname IS 'ClassName der Klasse, die für den Filter verwendet wird';


--
-- Name: COLUMN filter.parameters; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.filter.parameters IS 'JSON mit Filterspezifischen Einstellungen';


--
-- Name: filter_id_seq; Type: SEQUENCE; Schema: public; Owner: darcnews
--

CREATE SEQUENCE public.filter_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.filter_id_seq OWNER TO darcnews;

--
-- Name: filter_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: darcnews
--

ALTER SEQUENCE public.filter_id_seq OWNED BY public.filter.id;


--
-- Name: filteredby; Type: TABLE; Schema: public; Owner: darcnews
--

CREATE TABLE public.filteredby (
    inputmessageid bigint NOT NULL,
    filterid integer NOT NULL,
    state smallint NOT NULL
);


ALTER TABLE public.filteredby OWNER TO darcnews;

--
-- Name: TABLE filteredby; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON TABLE public.filteredby IS 'Filteraktivität';


--
-- Name: formattedby; Type: TABLE; Schema: public; Owner: darcnews
--

CREATE TABLE public.formattedby (
    inputmessageid bigint NOT NULL,
    filterid integer NOT NULL,
    formatterid integer NOT NULL,
    state smallint DEFAULT 0 NOT NULL
);


ALTER TABLE public.formattedby OWNER TO darcnews;

--
-- Name: TABLE formattedby; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON TABLE public.formattedby IS 'Formatteraktivität';


--
-- Name: COLUMN formattedby.inputmessageid; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.formattedby.inputmessageid IS 'ID der eingehenden Nachricht';


--
-- Name: COLUMN formattedby.filterid; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.formattedby.filterid IS 'ID des Filters, der die Nachricht verarbeitet hat';


--
-- Name: COLUMN formattedby.formatterid; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.formattedby.formatterid IS 'ID des Formatters, der die Nachricht verarbeitet hat';


--
-- Name: COLUMN formattedby.state; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.formattedby.state IS 'State der Verarbeitung, siehe _ProcessedByState';


--
-- Name: source; Type: TABLE; Schema: public; Owner: darcnews
--

CREATE TABLE public.source (
    id integer NOT NULL,
    name character varying(64) NOT NULL,
    enabled boolean DEFAULT false NOT NULL,
    classname character varying(64) NOT NULL,
    parameters text
);


ALTER TABLE public.source OWNER TO darcnews;

--
-- Name: TABLE source; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON TABLE public.source IS 'Input Source';


--
-- Name: COLUMN source.id; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.source.id IS 'ID des Handlers';


--
-- Name: COLUMN source.name; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.source.name IS 'Name des Handlers';


--
-- Name: COLUMN source.enabled; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.source.enabled IS 'Flag, ob der Handler aktiv ist';


--
-- Name: COLUMN source.classname; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.source.classname IS 'ClassName der Klasse, die für den Filter verwendet wird';


--
-- Name: COLUMN source.parameters; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.source.parameters IS 'JSON mit Filterspezifischen Einstellungen';


--
-- Name: handler_id_seq; Type: SEQUENCE; Schema: public; Owner: darcnews
--

CREATE SEQUENCE public.handler_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.handler_id_seq OWNER TO darcnews;

--
-- Name: handler_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: darcnews
--

ALTER SEQUENCE public.handler_id_seq OWNED BY public.source.id;


--
-- Name: inputmessage; Type: TABLE; Schema: public; Owner: darcnews
--

CREATE TABLE public.inputmessage (
    id bigint NOT NULL,
    createdat timestamp without time zone NOT NULL,
    sourceid bigint NOT NULL,
    uniqueid character varying(128) NOT NULL,
    state smallint DEFAULT 0 NOT NULL
);


ALTER TABLE public.inputmessage OWNER TO darcnews;

--
-- Name: TABLE inputmessage; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON TABLE public.inputmessage IS 'Rumpfdaten der Nachrichten und ewige History';


--
-- Name: COLUMN inputmessage.id; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.inputmessage.id IS 'ID der Nachricht';


--
-- Name: COLUMN inputmessage.createdat; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.inputmessage.createdat IS 'Timestamp der Empfängnis';


--
-- Name: COLUMN inputmessage.sourceid; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.inputmessage.sourceid IS 'ID des Handlers der die Nachricht empfangen hat';


--
-- Name: COLUMN inputmessage.uniqueid; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.inputmessage.uniqueid IS 'ID der Nachricht im Quellsystem oder Hash der Nachricht';


--
-- Name: COLUMN inputmessage.state; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.inputmessage.state IS 'Status der Nachricht';


--
-- Name: inputmessagedata; Type: TABLE; Schema: public; Owner: darcnews
--

CREATE TABLE public.inputmessagedata (
    id bigint NOT NULL,
    titel character varying(1000),
    teaser character varying(2000),
    text text NOT NULL,
    permalink character varying(256),
    image bytea,
    metadata text
);


ALTER TABLE public.inputmessagedata OWNER TO darcnews;

--
-- Name: TABLE inputmessagedata; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON TABLE public.inputmessagedata IS 'Enthält den Inhalt einer Nachricht';


--
-- Name: COLUMN inputmessagedata.id; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.inputmessagedata.id IS 'ID aus Tabelle "Message"';


--
-- Name: COLUMN inputmessagedata.titel; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.inputmessagedata.titel IS 'Titel der Nachricht';


--
-- Name: COLUMN inputmessagedata.teaser; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.inputmessagedata.teaser IS 'Optional: Einelitung oder Teaser';


--
-- Name: COLUMN inputmessagedata.text; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.inputmessagedata.text IS 'Inhalt der Nachricht';


--
-- Name: COLUMN inputmessagedata.permalink; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.inputmessagedata.permalink IS 'URL die auf die Nachricht verweist';


--
-- Name: COLUMN inputmessagedata.image; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.inputmessagedata.image IS 'Binäre Bilddaten';


--
-- Name: COLUMN inputmessagedata.metadata; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.inputmessagedata.metadata IS 'Optional: JSON-Metadaten';


--
-- Name: message_id_seq; Type: SEQUENCE; Schema: public; Owner: darcnews
--

CREATE SEQUENCE public.message_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.message_id_seq OWNER TO darcnews;

--
-- Name: message_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: darcnews
--

ALTER SEQUENCE public.message_id_seq OWNED BY public.inputmessage.id;


--
-- Name: outputmessage; Type: TABLE; Schema: public; Owner: darcnews
--

CREATE TABLE public.outputmessage (
    id bigint NOT NULL,
    createdat timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    formatterid integer NOT NULL,
    channelid integer NOT NULL,
    inputmessageid bigint NOT NULL,
    state smallint DEFAULT 0 NOT NULL,
    sequence integer NOT NULL,
    uniqueid character varying(128)
);


ALTER TABLE public.outputmessage OWNER TO darcnews;

--
-- Name: TABLE outputmessage; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON TABLE public.outputmessage IS 'Rumpdaten ausgehender Nachrichten';


--
-- Name: COLUMN outputmessage.id; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessage.id IS 'ID der ausgehenden Nachricht';


--
-- Name: COLUMN outputmessage.createdat; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessage.createdat IS 'Timestamp der Erzeugung';


--
-- Name: COLUMN outputmessage.formatterid; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessage.formatterid IS 'ID des Formatters, der die Nachricht erstellt hat';


--
-- Name: COLUMN outputmessage.channelid; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessage.channelid IS 'ID des Channels, für den die Nachricht ist';


--
-- Name: COLUMN outputmessage.inputmessageid; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessage.inputmessageid IS 'Referenz auf die InputMessage, wegen der diese Nachricht erzeugt wurde';


--
-- Name: COLUMN outputmessage.state; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessage.state IS 'Status der Nachricht 0 = Neu, 1 = Verarbeitet';


--
-- Name: COLUMN outputmessage.sequence; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessage.sequence IS 'Sequenznummer der Nachricht, falls aus einer Quellnachricht mehrere Zielnachrichten erzeugt werden';


--
-- Name: COLUMN outputmessage.uniqueid; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessage.uniqueid IS 'ID der Nachricht im Zielsystem oder Hash der Nachricht';


--
-- Name: outputmessage_id_seq; Type: SEQUENCE; Schema: public; Owner: darcnews
--

CREATE SEQUENCE public.outputmessage_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.outputmessage_id_seq OWNER TO darcnews;

--
-- Name: outputmessage_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: darcnews
--

ALTER SEQUENCE public.outputmessage_id_seq OWNED BY public.outputmessage.id;


--
-- Name: outputmessagedata; Type: TABLE; Schema: public; Owner: darcnews
--

CREATE TABLE public.outputmessagedata (
    id bigint NOT NULL,
    titel character varying(1000),
    teaser character varying(2000),
    text text NOT NULL,
    permalink character varying(256),
    image bytea,
    metadata text
);


ALTER TABLE public.outputmessagedata OWNER TO darcnews;

--
-- Name: TABLE outputmessagedata; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON TABLE public.outputmessagedata IS 'Inhalt der ausgehenden Nachricht';


--
-- Name: COLUMN outputmessagedata.id; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessagedata.id IS 'ID aus der Tabelle "OutputMessage"';


--
-- Name: COLUMN outputmessagedata.titel; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessagedata.titel IS 'Optional: Titel der Nachricht';


--
-- Name: COLUMN outputmessagedata.teaser; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessagedata.teaser IS 'Optional: Einleitung oder Teaser';


--
-- Name: COLUMN outputmessagedata.text; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessagedata.text IS 'Optional: Einelitung oder Teaser';


--
-- Name: COLUMN outputmessagedata.permalink; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessagedata.permalink IS 'URL die auf die Nachricht verweist';


--
-- Name: COLUMN outputmessagedata.image; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessagedata.image IS 'Binäre Bilddaten';


--
-- Name: COLUMN outputmessagedata.metadata; Type: COMMENT; Schema: public; Owner: darcnews
--

COMMENT ON COLUMN public.outputmessagedata.metadata IS 'Optional: JSON-Metadaten';


--
-- Name: channel id; Type: DEFAULT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.channel ALTER COLUMN id SET DEFAULT nextval('public.channel_id_seq'::regclass);


--
-- Name: filter id; Type: DEFAULT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.filter ALTER COLUMN id SET DEFAULT nextval('public.filter_id_seq'::regclass);


--
-- Name: formatter id; Type: DEFAULT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.formatter ALTER COLUMN id SET DEFAULT nextval('public."Formatter_id_seq"'::regclass);


--
-- Name: inputmessage id; Type: DEFAULT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.inputmessage ALTER COLUMN id SET DEFAULT nextval('public.message_id_seq'::regclass);


--
-- Name: outputmessage id; Type: DEFAULT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.outputmessage ALTER COLUMN id SET DEFAULT nextval('public.outputmessage_id_seq'::regclass);


--
-- Name: source id; Type: DEFAULT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.source ALTER COLUMN id SET DEFAULT nextval('public.handler_id_seq'::regclass);


--
-- Data for Name: _bystate; Type: TABLE DATA; Schema: public; Owner: darcnews
--

INSERT INTO public._bystate VALUES (0, 'No Match');
INSERT INTO public._bystate VALUES (-1, 'Error');
INSERT INTO public._bystate VALUES (1, 'Match/Success');
INSERT INTO public._bystate VALUES (2, 'CatchUp');


--
-- Data for Name: _messagestate; Type: TABLE DATA; Schema: public; Owner: darcnews
--

INSERT INTO public._messagestate VALUES (0, 'Neue Nachricht');
INSERT INTO public._messagestate VALUES (1, 'Nachricht verarbeitet');
INSERT INTO public._messagestate VALUES (2, 'Nachrichtenbody gelöscht');
INSERT INTO public._messagestate VALUES (3, 'durch CatchUp abgespeichert');
INSERT INTO public._messagestate VALUES (-1, 'Fehler bei der Verarbeitung');

--
-- Name: channel Channel_pkey; Type: CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.channel
    ADD CONSTRAINT "Channel_pkey" PRIMARY KEY (id);


--
-- Name: filter Filter_pkey; Type: CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.filter
    ADD CONSTRAINT "Filter_pkey" PRIMARY KEY (id);


--
-- Name: formatter Formatter_pkey; Type: CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.formatter
    ADD CONSTRAINT "Formatter_pkey" PRIMARY KEY (id);


--
-- Name: source Handler_pkey; Type: CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.source
    ADD CONSTRAINT "Handler_pkey" PRIMARY KEY (id);


--
-- Name: inputmessage Message_pkey; Type: CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.inputmessage
    ADD CONSTRAINT "Message_pkey" PRIMARY KEY (id);


--
-- Name: outputmessagedata OutputMessageData_pkey; Type: CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.outputmessagedata
    ADD CONSTRAINT "OutputMessageData_pkey" PRIMARY KEY (id);


--
-- Name: outputmessage OutputMessage_pkey; Type: CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.outputmessage
    ADD CONSTRAINT "OutputMessage_pkey" PRIMARY KEY (id);


--
-- Name: _messagestate _MessageState_pkey; Type: CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public._messagestate
    ADD CONSTRAINT "_MessageState_pkey" PRIMARY KEY (id);


--
-- Name: _bystate _ProcessedByState_pkey; Type: CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public._bystate
    ADD CONSTRAINT "_ProcessedByState_pkey" PRIMARY KEY (id);


--
-- Name: filteredby filteredby_pkey; Type: CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.filteredby
    ADD CONSTRAINT filteredby_pkey PRIMARY KEY (inputmessageid, filterid);


--
-- Name: formattedby formattedby_pkey; Type: CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.formattedby
    ADD CONSTRAINT formattedby_pkey PRIMARY KEY (inputmessageid, filterid, formatterid);


--
-- Name: InputMessage_SourceId_UniqueId; Type: INDEX; Schema: public; Owner: darcnews
--

CREATE INDEX "InputMessage_SourceId_UniqueId" ON public.inputmessage USING btree (sourceid, uniqueid);


--
-- Name: OutputMessage_InputMessageId-FormatterId; Type: INDEX; Schema: public; Owner: darcnews
--

CREATE INDEX "OutputMessage_InputMessageId-FormatterId" ON public.outputmessage USING btree (inputmessageid, formatterid);


--
-- Name: filteredby FilteredBy_FilterId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.filteredby
    ADD CONSTRAINT "FilteredBy_FilterId_fkey" FOREIGN KEY (filterid) REFERENCES public.filter(id) ON DELETE CASCADE;


--
-- Name: filteredby FilteredBy_InputMessageId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.filteredby
    ADD CONSTRAINT "FilteredBy_InputMessageId_fkey" FOREIGN KEY (inputmessageid) REFERENCES public.inputmessage(id) ON DELETE CASCADE;


--
-- Name: filteredby FilteredBy_State_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.filteredby
    ADD CONSTRAINT "FilteredBy_State_fkey" FOREIGN KEY (state) REFERENCES public._bystate(id);


--
-- Name: formatter Formatter_ChannelId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.formatter
    ADD CONSTRAINT "Formatter_ChannelId_fkey" FOREIGN KEY (channelid) REFERENCES public.channel(id) ON DELETE CASCADE;


--
-- Name: formatter Formatter_FilterId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.formatter
    ADD CONSTRAINT "Formatter_FilterId_fkey" FOREIGN KEY (filterid) REFERENCES public.filter(id) ON DELETE CASCADE;


--
-- Name: inputmessage InputMessage_SourceId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.inputmessage
    ADD CONSTRAINT "InputMessage_SourceId_fkey" FOREIGN KEY (sourceid) REFERENCES public.source(id) ON DELETE CASCADE;


--
-- Name: inputmessage InputMessage_State_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.inputmessage
    ADD CONSTRAINT "InputMessage_State_fkey" FOREIGN KEY (state) REFERENCES public._messagestate(id);


--
-- Name: outputmessagedata OutputMessageData_Id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.outputmessagedata
    ADD CONSTRAINT "OutputMessageData_Id_fkey" FOREIGN KEY (id) REFERENCES public.outputmessage(id) ON DELETE CASCADE;


--
-- Name: outputmessage OutputMessage_ChannelId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.outputmessage
    ADD CONSTRAINT "OutputMessage_ChannelId_fkey" FOREIGN KEY (channelid) REFERENCES public.channel(id) ON DELETE CASCADE;


--
-- Name: outputmessage OutputMessage_FormatterId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.outputmessage
    ADD CONSTRAINT "OutputMessage_FormatterId_fkey" FOREIGN KEY (formatterid) REFERENCES public.formatter(id) ON DELETE CASCADE;


--
-- Name: outputmessage OutputMessage_InputMessageId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.outputmessage
    ADD CONSTRAINT "OutputMessage_InputMessageId_fkey" FOREIGN KEY (inputmessageid) REFERENCES public.inputmessage(id) ON DELETE CASCADE;


--
-- Name: outputmessage OutputMessage_State_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.outputmessage
    ADD CONSTRAINT "OutputMessage_State_fkey" FOREIGN KEY (state) REFERENCES public._messagestate(id);


--
-- Name: formattedby ProcessedBy_FilterId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.formattedby
    ADD CONSTRAINT "ProcessedBy_FilterId_fkey" FOREIGN KEY (filterid) REFERENCES public.filter(id) ON DELETE CASCADE;


--
-- Name: formattedby ProcessedBy_FormatterId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.formattedby
    ADD CONSTRAINT "ProcessedBy_FormatterId_fkey" FOREIGN KEY (formatterid) REFERENCES public.formatter(id) ON DELETE CASCADE;


--
-- Name: formattedby ProcessedBy_InputMessageId_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.formattedby
    ADD CONSTRAINT "ProcessedBy_InputMessageId_fkey" FOREIGN KEY (inputmessageid) REFERENCES public.inputmessage(id) ON DELETE CASCADE;


--
-- Name: formattedby ProcessedBy_State_fkey; Type: FK CONSTRAINT; Schema: public; Owner: darcnews
--

ALTER TABLE ONLY public.formattedby
    ADD CONSTRAINT "ProcessedBy_State_fkey" FOREIGN KEY (state) REFERENCES public._bystate(id);


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE USAGE ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

