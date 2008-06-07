class EventsController < ApplicationController
  before_filter :login_required, :except => [:index, :list, :show]

  def index
    list
    render :action => 'list'
  end

  # GETs should be safe (see http://www.w3.org/2001/tag/doc/whenToUseGet.html)
  verify :method => :post, :only => [ :destroy, :create, :update ],
         :redirect_to => { :action => :list }

  def list
    @event_pages, @events = paginate :events, :conditions => published_sql(self.controller_name), :include => [:minutes], :order => "events.date DESC", :per_page => 10
    @page_title = "Events"
  end

  def show
    @event = Event.find(params[:id], :conditions => published_sql(self.controller_name))
    @recent_events = Event.find(:all, :conditions => published_sql(self.controller_name), :order => "events.created_at, events.id DESC", :limit => "10")
    @page_title = @event.title if @event
  end

  def new
    @event = Event.new
  end

  def create
    @event = Event.new(params[:event])
    @event.urlpath = @event.title.downcase.gsub(" ", "-")
    if @event.save
      flash[:notice] = 'Event was successfully created.'
      redirect_to :action => 'list'
    else
      render :action => 'new'
    end
  end

  def edit
    @event = Event.find(params[:id])
  end

  def update
    @event = Event.find(params[:id])
    if @event.update_attributes(params[:event])
      @event.urlpath = @event.title.downcase.gsub(" ", "-")
      @event.save
      flash[:notice] = 'Event was successfully updated.'
      redirect_to :action => 'show', :id => @event
    else
      render :action => 'edit'
    end
  end

  def destroy
    Event.find(params[:id]).destroy
    redirect_to :action => 'list'
  end
end
